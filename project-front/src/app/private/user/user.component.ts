import { Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ProviderService } from '../../services/provider.service';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { ActivatedRoute, Params, Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { WebSocketsService } from '../../services/web-sockets.service';
import { LocalstorageService } from '../../services/localstorage.service';

@Component({
  selector: 'app-user',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
  ],
  templateUrl: './user.component.html',
  styleUrl: './user.component.scss',
})
export class UserComponent {

  private _formbuilder = inject(FormBuilder)
  private _provider = inject(ProviderService)
  private _snackBar = inject(MatSnackBar)
  private _wsService = inject(WebSocketsService)
  private _router = inject(Router)
  private _activedRouter = inject(ActivatedRoute)
  private _storage = inject(LocalstorageService)

  id: string = ''
  isClient: boolean = false
  isEditMode: boolean = false

  // Lista de roles disponibles
  roles = [
    { name: 'Administrador', value: 0 },
    { name: 'Cajero', value: 1 },
    { name: 'Cocinero', value: 2 },
    { name: 'Cliente', value: 3 },
  ]

  // Formulario con validaciones
  formulario = this._formbuilder.group({
    idusers: [null],
    name: [null, [Validators.required]],
    password: [null, [Validators.required]],
    phone: [null],
    rol: [null, Validators.required],
    email: ['']
  })

  async ngOnInit() {

    // Detecta si es edicion o creacion
    this.isEditMode = this._router.url.includes('edit')

    // Si es creacion, no permite crear clientes
    if (!this.isEditMode) {
      this.roles = this.roles.filter(r => r.value !== 3)
    }

    // Detecta si el usuario logueado es cliente
    const currentUser = this._storage.getItem('user')
    if (currentUser && Number(currentUser.rol) === 3) {
      this.isClient = true
      this.formulario.get('rol')?.disable()
    }

    // Si esta en edicion, carga los datos del usuario
    if (this.isEditMode) {

      // La contraseña no es obligatoria al editar
      this.formulario.get('password')?.clearValidators()
      this.formulario.get('password')?.updateValueAndValidity()

      this._activedRouter.params.subscribe(async (params: Params) => {

        this.id = params['id']

        const response: any = await this._provider.request('GET', 'user/viewUser', { idusers: this.id })
        const user = Array.isArray(response) ? response[0] : response

        // No parchea la contraseña
        if (user?.password) delete user.password

        // Si es cliente, bloquea el rol
        if (Number(user?.rol) === 3) {
          this.roles = [...this.roles]
          this.formulario.get('rol')?.disable()
          this.isClient = true
        } else {
          this.roles = this.roles.filter(r => r.value !== 3)
        }

        this.formulario.patchValue(user)

        // Forza email vacio si no aplica
        if (!this.showEmail) {
          this.formulario.patchValue({ email: '' })
        }
      })
    }
  }

  async save() {

    const formData = this.formulario.getRawValue()

    if (!this.showEmail) formData.email = ''

    // --- MODO EDICION ---
    if (this.isEditMode) {

      if (this.formulario.valid) {

        const dataToSend = { ...formData, idusers: this.id }
        var data = await this._provider.request('PUT', 'user/updateUser', dataToSend)

        if (data) {

          // Si el usuario editado es el mismo que el logueado, se actualiza en localstorage
          const currentUser = this._storage.getItem('user')
          if (currentUser && String(currentUser.idusers) === String(this.id)) {
            const updatedUser = { ...data, token: currentUser.token }
            this._storage.setItem('user', updatedUser)
          }

          this._snackBar.open('Usuario Actualizado', '', { duration: 3000, verticalPosition: 'top' })

          if (this.isClient) {
            this._router.navigate(['private/menu'])
          } else {
            this._router.navigate(['private/user-view'])
          }

          this.formulario.reset()
        } else {
          this._snackBar.open('No es posible actualizar', '', { duration: 3000, verticalPosition: 'top' })
        }

      } else {
        this._snackBar.open('Formulario invalido', '', { duration: 3000, verticalPosition: 'top' })
        this.markFormInvalid()
      }

    } else {
      // --- MODO CREACION ---

      if (this.formulario.valid) {

        var data = await this._provider.request('POST', 'auth/signup', formData)

        if (data) {
          this._snackBar.open('Usuario Creado', '', { duration: 3000, verticalPosition: 'top' })
          this._router.navigate(['private/user-view'])
          this.formulario.reset()
        } else {
          this._snackBar.open('No es posible crear', '', { duration: 3000, verticalPosition: 'top' })
        }

      } else {
        this._snackBar.open('Formulario invalido', '', { duration: 3000, verticalPosition: 'top' })
        this.markFormInvalid()
      }
    }
  }

  async deleteUser() {

    var data = await this._provider.request('DELETE', 'user/deleteUser', { idusers: this.id })

    if (data) {
      this._wsService.request('usuarios', data)
      this._snackBar.open('Usuario Eliminado', '', { duration: 3000, verticalPosition: 'top' })
      this._router.navigate(['private/user-view'])
      this.formulario.reset()
    } else {
      this._snackBar.open('No es posible eliminar el usuario', '', { duration: 3000, verticalPosition: 'top' })
    }
  }

  // Marca todos los campos invalidos en rojo
  markFormInvalid() {
    document.querySelectorAll('.ng-invalid, .mat-mdc-radio-group.unselect')
      .forEach((element: Element) => element.classList.add('invalid'))
  }

  // Muestra o no el campo email dependiendo del rol
  get showEmail(): boolean {
    const selectedRol = this.formulario.get('rol')?.value
    return Number(selectedRol) === 3
  }
}
