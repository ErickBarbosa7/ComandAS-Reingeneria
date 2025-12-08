import { Component, inject } from '@angular/core';
import {MatIconModule} from '@angular/material/icon';
import {MatInputModule} from '@angular/material/input';
import {MatFormFieldModule} from '@angular/material/form-field';
import { FormBuilder, FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Router } from '@angular/router';
import { ProviderService } from '../../../services/provider.service';
import { LocalstorageService } from '../../../services/localstorage.service';
import { MatDialog } from '@angular/material/dialog';
import { OrderDetailComponent } from '../../../private/order-detail/order-detail.component';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-sign-in',
  standalone: true,
  imports: [MatFormFieldModule, MatInputModule, MatIconModule, HttpClientModule, FormsModule, ReactiveFormsModule,],
  templateUrl: './sign-in.component.html',
  styleUrl: './sign-in.component.scss'
})
export class SignInComponent {
  private _form_builder: FormBuilder = inject(FormBuilder)
  private _http: HttpClient = inject(HttpClient)
  private _router: Router = inject(Router)
  private _provider: ProviderService = inject(ProviderService)
  private _localstorage: LocalstorageService = inject(LocalstorageService)
  private dialog: MatDialog = inject(MatDialog)
  private _snackBar: MatSnackBar = inject(MatSnackBar)

  req: any

  // Formulario de inicio de sesion
  form_signin: FormGroup = this._form_builder.group({
    name: [null, Validators.required],
    password: [null, Validators.required]
  })

  // Navega al registro
  goToSignUp() {
    this._router.navigate(['auth/sign-up'])
  }

  // Metodo principal del login
  async signin() {

    // Solo se ejecuta si los campos son validos
    if (this.form_signin.valid) {

      // Mandamos la info al endpoint de signin
      this.req = await this._provider.request('POST', 'auth/signin', this.form_signin.value)

      // Si hay error o no responde, mostramos mensaje
      if (!this.req || this.req.error) {
        this._snackBar.open("Credenciales incorrectas", "Cerrar", { duration: 3000 })
        return
      }

      // Guardamos los datos del usuario en localstorage
      this._localstorage.setItem('user', this.req)

      // Obtenemos el rol (viene como numero)
      const rol = Number(this._localstorage.getItem('user').rol)

      // Redirige dependiendo del rol
      switch (rol) {
        case 0:
          this._router.navigate(['private/menu'])
          break
        case 1:
          this._router.navigate(['private/orders-view'])
          break
        case 2:
          this._router.navigate(['private/chef-order-view'])
          this.actualOrder()
          break
        case 3:
          this._router.navigate(['private/menu'])
          break
        case 4: // Cliente
          this._router.navigate(['private/menu'])
          break
      }
    }
  }

  // Si el usuario trae una orden activa, abre el modal con los detalles
  async actualOrder() {
    const orderExist = this._localstorage.getItem('user').actual_order
    console.log(orderExist)

    if (orderExist) {
      this.dialog.open(OrderDetailComponent, {
        data: { idorder: orderExist }
      })
    }
  }
}
