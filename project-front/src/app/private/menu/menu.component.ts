import { Component, ViewChild, inject, ChangeDetectorRef, OnInit } from '@angular/core';
import {
  FormArray,
  FormBuilder,
  FormGroup,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { ProviderService } from '../../services/provider.service';
import { Product } from '../../interfaces/product.model';
import { MatDrawer, MatSidenavModule } from '@angular/material/sidenav';
import { CurrencyPipe, KeyValuePipe, NgClass, NgStyle } from '@angular/common';
import { MatSelectModule } from '@angular/material/select';
import { Ingredient } from '../../interfaces/ingredient.model';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { OrderService } from '../../services/order.service';
import { RouterLink } from '@angular/router';
import { MatCard, MatCardActions, MatCardContent } from '@angular/material/card';

@Component({
  selector: 'app-menu',
  standalone: true,
  imports: [
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    FormsModule,
    ReactiveFormsModule,
    MatSidenavModule,
    KeyValuePipe,
    MatSelectModule,
    MatCheckboxModule,
    NgStyle,
    CurrencyPipe,
    NgClass,
    RouterLink,
    MatCard,
    MatCardActions,
    MatCardContent
  ],
  templateUrl: './menu.component.html',
  styleUrl: './menu.component.scss',
})
export class MenuComponent implements OnInit {
  // servicio para pedir datos al backend
  private _provider: ProviderService = inject(ProviderService);

  // servicio que maneja toda la logica del pedido
  public _order: OrderService = inject(OrderService);

  // se usa para forzar que Angular actualice la vista
  private _cdr: ChangeDetectorRef = inject(ChangeDetectorRef);

  // referencia al sidenav donde se muestran los detalles del pedido
  @ViewChild('barraComentarios') barraComentarios: MatDrawer | undefined;

  // lista completa del menu que llega desde la API
  menu: Product[] = [];

  async ngOnInit() {
    try {
      // carga el menu desde el servidor
      this.menu = await this._provider.request('GET', 'menu/viewIngredients');
      console.log('Menu cargado:', this.menu);

      // obliga a Angular a refrescar para que los productos aparezcan
      this._cdr.detectChanges();

      // escucha cambios en la orden para cerrar el sidenav cuando se queda vacio
      this._order.formOrder.controls['order_details'].valueChanges.subscribe(
        (value: any) => {
          if (!value.length) this.barraComentarios?.close();
        }
      );

      console.log('Detalles actuales de la orden:', this.orderDetailsArray().value);

      // si ya habia productos en el pedido, abre el panel al cargar
      if (this.orderDetailsArray().value.length && !this.orderEmpty()) {
        setTimeout(() => {
          this.barraComentarios?.open();
        }, 100);
      }
    } catch (error) {
      console.error('Error cargando el menu:', error);
    }
  }

  // filtra los productos por categoria
  filterByCategory(id_category: string): Product[] {
    const products = this.menu.filter(
      (product: Product) => product.category_idcategory == id_category
    );

    if (products.length === 0 && this.menu.length > 0) {
      console.warn(`filterByCategory: No se encontraron productos para la categoria ${id_category}`);
    }

    return products;
  }

  // devuelve un producto por ID
  filterByProduct(id_product: string): Product {
    return (
      this.menu.find((product: Product) => product.idproducts == id_product) ||
      ({} as Product)
    );
  }

  // devuelve los ingredientes requeridos o extras segun su tipo
  filterByIngredient(
    key: 'required' | 'extra',
    value: 0 | 1,
    ingredients: Ingredient[] | undefined,
    idproduct: string,
    amount: number
  ): Ingredient[] | undefined {
    if (!ingredients) return [];

    const type = value == 0 ? 1 : 0;

    return ingredients.filter(
      (ingredient: Ingredient) =>
        ingredient[key] == value &&
        !this.filterExtraExceptions(idproduct, amount, type).includes(
          ingredient.idingredients
        )
    );
  }

  // agrega un producto al pedido
  addProduct(
    products_idproducts: string,
    price: number,
    name: string,
    name_category: string
  ) {
    this.barraComentarios?.open();

    (this._order.formOrder.controls['order_details'] as FormArray).push(
      this._order.orderDetails(products_idproducts, price, name, name_category)
    );

    console.log(this._order.formOrder.value);
  }

  // elimina un producto por ID
  removeProduct(idproduct: string) {
    const index = this._order.formOrder.controls['order_details'].value.findIndex(
      (product: any) => product.products_idproducts == idproduct
    );

    if (index != -1)
      (this._order.formOrder.controls['order_details'] as FormArray).removeAt(index);

    console.log(this._order.formOrder.value);
  }

  // agrega o quita ingredientes
  addIngredient(
    idproduct: string,
    idingredient: string,
    type: 0 | 1,
    event: any,
    amount: number,
    name: string,
    price: number
  ) {
    const index = this._order.formOrder.controls['order_details'].value.findIndex(
      (product: any) =>
        product.products_idproducts == idproduct && product.amount == amount
    );

    if (index === -1) return;

    // agregar ingrediente
    if (event) {
      (
        (
          (this._order.formOrder.controls['order_details'] as FormArray).at(
            index
          ) as FormGroup
        ).controls['not_ingredient'] as FormArray
      ).push(this._order.notIngredients(idingredient, type, name, price));

    // quitar ingrediente
    } else {
      const indexIngredient = (
        (this._order.formOrder.controls['order_details'] as FormArray).at(
          index
        ) as FormGroup
      ).controls['not_ingredient'].value.findIndex(
        (ingredient: any) =>
          ingredient.ingredients_idingredients == idingredient
      );

      if (indexIngredient != -1)
        (
          (
            (this._order.formOrder.controls['order_details'] as FormArray).at(
              index
            ) as FormGroup
          ).controls['not_ingredient'] as FormArray
        ).removeAt(indexIngredient);
    }
  }

  // devuelve los ingredientes que no deben mostrarse como seleccionables
  filterExtraExceptions(idproduct: string, amount: number, type: 0 | 1): string[] {
    const index = this._order.formOrder.controls['order_details'].value.findIndex(
      (product: any) =>
        product.products_idproducts == idproduct && product.amount == amount
    );

    if (index === -1) return [];

    return (
      (
        (this._order.formOrder.controls['order_details'] as FormArray).at(index) as FormGroup
      ).controls['not_ingredient'] as FormArray
    ).value
      .filter((ingredient: any) => ingredient.type == type)
      .map((ingredient: any) => ingredient.ingredients_idingredients);
  }

  // cuenta cuantas veces se agrego un producto
  amount(id: string): number {
    return this._order.formOrder.controls['order_details'].value.filter(
      (product: any) => product.products_idproducts == id
    ).length;
  }

  // atajo para devolver el FormArray del pedido
  orderDetailsArray() {
    return this._order.formOrder.controls['order_details'] as FormArray;
  }

  // valida si un ingrediente esta seleccionado
  ingredientsSelected(index: number, type: 0 | 1, name: string) {
    const group = this.orderDetailsArray().at(index) as FormGroup;
    if (!group) return false;

    return (group.controls['not_ingredient'] as FormArray)
      .value
      .map((ingredientSelected: any) => {
        if (ingredientSelected.type == type) return ingredientSelected.name;
        return;
      })
      .filter((id: any) => id != undefined)
      .includes(name);
  }

  // valida si hay alguna hamburguesa en el pedido
  hasHamburgerSelected(): boolean {
    const orderDetails = this.orderDetailsArray().value;

    return orderDetails.some((item: any) => {
      const product = this.filterByProduct(item.products_idproducts);
      return product && product.category_idcategory == '1';
    });
  }

  // valida si el pedido esta vacio
  orderEmpty() {
    return this.orderDetailsArray()
      .value.map((order: any) => Object.values(order))
      .flat()
      .filter((item: any) => !Array.isArray(item))
      .every((item: any) => item == null);
  }
}
