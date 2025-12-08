import { Component, inject } from '@angular/core'
import { OrderService } from '../../services/order.service'
import { CurrencyPipe } from '@angular/common'
import { MatFormFieldModule } from '@angular/material/form-field'
import { MatRadioChange, MatRadioModule } from '@angular/material/radio'
import {
  AbstractControl,
  FormArray,
  FormControl,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms'
import { MatInputModule } from '@angular/material/input'
import { ProviderService } from '../../services/provider.service'
import { Router, RouterLink } from '@angular/router'
import { WebSocketsService } from '../../services/web-sockets.service'
import { MatSnackBar } from '@angular/material/snack-bar'
import { LocalstorageService } from '../../services/localstorage.service'
import { MatIconModule } from '@angular/material/icon'

@Component({
  selector: 'app-order-view',
  standalone: true,
  imports: [
    CurrencyPipe,
    MatFormFieldModule,
    MatRadioModule,
    FormsModule,
    ReactiveFormsModule,
    MatInputModule,
    RouterLink,
    MatIconModule
  ],
  templateUrl: './order-view.component.html',
  styleUrl: './order-view.component.scss',
})
export class OrderViewComponent {

  // Servicios que se inyectan para usar peticiones, rutas, socket y datos locales
  private _provider: ProviderService = inject(ProviderService)
  private _router: Router = inject(Router)
  public _order: OrderService = inject(OrderService)
  private _wsService: WebSocketsService = inject(WebSocketsService)
  private _snackBar: MatSnackBar = inject(MatSnackBar)
  private _localStorage: LocalstorageService = inject(LocalstorageService)

  // Flags del componente
  isClient: boolean = false
  defaultOrigin: number = 1 // 1 comer aqui, 0 llevar

  async ngOnInit() {
    const user = this._localStorage.getItem('user')
    
    // Si el usuario es cliente forzamos reglas especiales
    if (user && Number(user.rol) === 3) {
        this.isClient = true
        this.defaultOrigin = 0 // el cliente siempre pide para llevar

        // Se coloca su nombre directo en el form
        this._order.formOrder.patchValue({ client: user.name })
        
        // Se ajustan los productos a tipo llevar
        this.updateAllProductsOrigin(0)
    }
  }

  // Cambia el tipo de todos los productos en el pedido
  updateAllProductsOrigin(type: number) {
    this.eachProduct().controls.forEach((product: AbstractControl) => {
        const productAux: FormGroup = product as FormGroup
        productAux.controls['order_type'].patchValue(type)
    })
  }

  // Filtra ingredientes segun tipo de extra o normal
  filterExtras(item: any, type: 0 | 1) {
    return item.not_ingredient.filter(
      (ingredient: any) => ingredient.type == type
    )
  }

  // Total de productos base
  totalProducts() {
    return this.eachProduct()
      .value.map((product: any) => Number(product.unit_price))
      .reduce((previous: number, current: number) => previous + current, 0)
  }

  // Total de extras adicionales
  totalExtras() {
    return this.eachProduct()
      .value.map((product: any) =>
        product.not_ingredient
          .map((ingredient: any) => Number(ingredient.price))
          .reduce((previous: number, current: number) => previous + current, 0)
      )
      .reduce((previous: number, current: number) => previous + current, 0)
  }

  // Total general
  totalOrder() {
    this._order.formOrder.controls['total'].patchValue(this.totalProducts() + this.totalExtras())
    return this.totalProducts() + this.totalExtras()
  }

  // Shortcut para acceder al form de radio
  radioForm() {
    return this._order.formOrder.controls['order_details'] as FormGroup
  }

  // Shortcut para recorrer cada producto
  eachProduct() {
    return this._order.formOrder.controls['order_details'] as FormArray
  }

  // Cambia el tipo de todos los productos usando radio
  selected(event: MatRadioChange) {
    this.eachProduct().controls.forEach((product: AbstractControl) => {
      const productAux: FormGroup = product as FormGroup
      productAux.controls['order_type'].patchValue(event.value)
    })
  }

  // Envia la orden al backend
  async placeOrder() {
    console.log(this._order.formOrder.value)
    
    // Se agrega el id de usuario que genera la orden
    this._order.formOrder.controls['users_idusers']
      .patchValue(this._localStorage.getItem('user').idusers)

    // Validacion de formulario y conexion socket
    if (this._order.formOrder.valid && this._wsService.socketStatus) {

      this.totalOrder()
      var data = await this._provider.request('POST', 'order/createOrder', this._order.formOrder.value)
      
      if (data) {
        // Notificacion por socket
        await this._wsService.request('comandas', data)
        
        this._snackBar.open("Orden realizada", "", { duration: 3000, verticalPosition: 'top' })
        
        // Se regresa a la vista de ordenes
        this._router.navigate(['private/orders-view'])

        // Limpieza del form y productos
        this._order.formOrder.reset()
        while (this.orderDetailsArray().length !== 0) {
          this.orderDetailsArray().removeAt(0)
        }
      } else {
        this._snackBar.open("No se realizo la orden", "", { duration: 3000, verticalPosition: 'top' })
      }

    } else {

      // Error por formulario o socket
      this._snackBar.open("No es posible realizar la orden", "", { duration: 3000, verticalPosition: 'top' })
      
      document.querySelectorAll('.ng-invalid, .mat-mdc-radio-group.unselect')
        .forEach((element: Element) => element.classList.add('invalid'))
    }
  }

  // Retorna el FormArray de productos
  orderDetailsArray() {
    return this._order.formOrder.controls['order_details'] as FormArray
  }

  // Elimina un producto por indice
  deleteProduct(index: number) {
    this.eachProduct().removeAt(index)
  }

}
