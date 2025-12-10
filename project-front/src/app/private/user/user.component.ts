import { CommonModule } from '@angular/common';
import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { LocalstorageService } from '../../services/localstorage.service';
import { ProviderService } from '../../services/provider.service';
import { WebSocketsService } from '../../services/web-sockets.service';

@Component({
  selector: 'app-user',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule
  ],
  templateUrl: './user.component.html',
  styleUrl: './user.component.scss',
})
export class UserComponent implements OnInit {

  private _formbuilder = inject(FormBuilder);
  private _provider = inject(ProviderService);
  private _snackBar = inject(MatSnackBar);
  private _wsService = inject(WebSocketsService);
  private _router = inject(Router);
  private _activedRouter = inject(ActivatedRoute);
  private _storage = inject(LocalstorageService);

  id: string = '';
  isClient: boolean = false;
  isEditMode: boolean = false;

  // Lista de roles disponibles
  roles = [
    { name: 'Administrador', value: 0 },
    { name: 'Cajero / Mesero', value: 1 },
    { name: 'Cocinero', value: 2 },
    { name: 'Cliente', value: 3 },
  ];

  // Formulario con validaciones
  formulario = this._formbuilder.group({
    idusers: [null],
    name: [null, [Validators.required]],
    password: [null, [Validators.required]], // Se vuelve opcional en edición
    phone: [null],
    rol: [null, Validators.required],
    email: [''] // Campo opcional
  });

  async ngOnInit() {
    // 1. Detectar modo (Edición vs Creación)
    this.isEditMode = this._router.url.includes('edit');

    // 2. Regla de Negocio: Al CREAR, el admin NO puede crear Clientes (Rol 3)
    if (!this.isEditMode) {
      this.roles = this.roles.filter(r => r.value !== 3);
    }

    // 3. Detectar si el usuario LOGUEADO es un Cliente (para proteger su propio perfil)
    const currentUser = this._storage.getItem('user');
    if (currentUser && Number(currentUser.rol) === 3) {
      this.isClient = true;
      this.formulario.get('rol')?.disable(); // Cliente no puede cambiarse el rol
    }

    // 4. Si es Edición, cargar datos
    if (this.isEditMode) {
      // La contraseña no es obligatoria al editar (si se deja vacía, no se cambia)
      this.formulario.get('password')?.clearValidators();
      this.formulario.get('password')?.updateValueAndValidity();

      this._activedRouter.params.subscribe(async (params: Params) => {
        this.id = params['id'];

        try {
          const response: any = await this._provider.request('GET', 'user/viewUser', { idusers: this.id });
          const user = Array.isArray(response) ? response[0] : response;

          if (user) {
            // Limpiamos password para que no aparezca el hash en el input
            if (user.password) delete user.password;

            // Lógica visual para rol de cliente
            if (Number(user.rol) === 3) {
              // Si estamos editando a un cliente, permitimos ver el rol 3 en la lista
              // (pero deshabilitado si se requiere)
              // Aquí restauramos la lista completa para que aparezca "Cliente"
              this.roles = [
                { name: 'Administrador', value: 0 },
                { name: 'Cajero', value: 1 },
                { name: 'Cocinero', value: 2 },
                { name: 'Cliente', value: 3 },
              ];
              this.formulario.get('rol')?.disable();
              this.isClient = true;
            } else {
              // Si es staff, filtramos cliente
              this.roles = this.roles.filter(r => r.value !== 3);
            }

            this.formulario.patchValue(user);
          }
        } catch (error) {
          console.error(error);
        }
      });
    }
  }

  async save() {
    // Obtenemos valores (incluyendo deshabilitados si los hubiera)
    const formData = this.formulario.getRawValue();

    // Limpieza opcional
    if (!this.showEmail) formData.email = '';

    try {
      // --- MODO EDICION (UPDATE) ---
      if (this.isEditMode) {
        if (this.formulario.valid) {
          const dataToSend = { ...formData, idusers: this.id };
          
          // Usamos el endpoint correcto: user/update
          // Nota: Asegúrate de tener esta ruta en Router.php: "user/update" => [UserController::class, "updateUser", 1]
          const data: any = await this._provider.request('POST', 'user/update', dataToSend);

          if (data && !data.error) {
            // Actualizar LocalStorage si me edité a mí mismo
            const currentUser = this._storage.getItem('user');
            if (currentUser && String(currentUser.idusers) === String(this.id)) {
              const updatedUser = { ...currentUser, ...dataToSend };
              // Si el password venía vacío, no lo sobreescribimos en el objeto local (aunque el backend ya lo manejó)
              this._storage.setItem('user', updatedUser);
            }

            this._snackBar.open('Usuario Actualizado', 'Cerrar', { duration: 3000, verticalPosition: 'top' });
            
            // Redirección inteligente
            if (this.isClient) {
              this._router.navigate(['private/menu']);
            } else {
              this._router.navigate(['private/user-view']);
            }
          } else {
            this._snackBar.open(data.msg || 'No se pudo actualizar', 'Cerrar', { duration: 3000, verticalPosition: 'top' });
          }
        } else {
          this.markFormInvalid();
        }

      } else {
        // --- MODO CREACION (CREATE) ---
        if (this.formulario.valid) {
          
          // CAMBIO CRÍTICO: Usar 'user/create' en lugar de 'auth/signup'
          // auth/signup fuerza rol Cliente. user/create respeta el rol que seleccionamos (Cocinero/Cajero)
          const data: any = await this._provider.request('POST', 'user/create', formData);

          if (data && !data.error) {
            this._snackBar.open('Usuario Creado Exitosamente', 'Cerrar', { duration: 3000, verticalPosition: 'top' });
            this._router.navigate(['private/user-view']);
          } else {
            this._snackBar.open(data.msg || 'No se pudo crear el usuario', 'Cerrar', { duration: 3000, verticalPosition: 'top' });
          }
        } else {
          this.markFormInvalid();
        }
      }
    } catch (error) {
      console.error(error);
      this._snackBar.open('Error de comunicación con el servidor', 'Cerrar', { duration: 3000 });
    }
  }

  async deleteUser() {
    if(!confirm("¿Estás seguro de eliminar este usuario?")) return;

    try {
      // Usamos el endpoint correcto: user/delete
      const data: any = await this._provider.request('DELETE', 'user/delete', { idusers: this.id });

      if (data && !data.error) {
        // Notificar via socket si es necesario
        // this._wsService.emit('usuarios_actualizados', data); 
        
        this._snackBar.open('Usuario Eliminado', 'Cerrar', { duration: 3000, verticalPosition: 'top' });
        this._router.navigate(['private/user-view']);
      } else {
        // Mostrar mensaje del backend (Ej: "No se puede eliminar porque tiene ventas")
        this._snackBar.open(data.msg || 'No es posible eliminar el usuario', 'Cerrar', { duration: 5000, verticalPosition: 'top' });
      }
    } catch (error) {
      console.error(error);
    }
  }

  markFormInvalid() {
    this._snackBar.open('Formulario inválido, revisa los campos rojos', 'Cerrar', { duration: 3000 });
    this.formulario.markAllAsTouched();
  }

  get showEmail(): boolean {
    const selectedRol = this.formulario.get('rol')?.value;
    return Number(selectedRol) === 3;
  }
}