import { Component, inject } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatIconModule } from '@angular/material/icon';
import { Router, RouterLink } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ProviderService } from '../../../services/provider.service';

@Component({
  selector: 'app-sign-up',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatIconModule,
    RouterLink 
  ],
  templateUrl: './sign-up.component.html',
  styleUrl: './sign-up.component.scss'
})

export class SignUpComponent {
  private _formBuilder: FormBuilder = inject(FormBuilder)
  private _provider: ProviderService = inject(ProviderService)
  private _router: Router = inject(Router)
  private _snackBar: MatSnackBar = inject(MatSnackBar)

  // Creamos el formulario con validaciones basicas
  form: FormGroup = this._formBuilder.group({
    name: ['', [Validators.required]],
    phone: ['', [Validators.required, Validators.minLength(10)]],
    password: ['', [Validators.required]],
    email: ['', [Validators.required, Validators.email]]
  })

  // Navega al login
  goToSignIn() {
    this._router.navigate(['auth/sign-in'])
  }

  // Metodo principal del registro
  async signup() {
    // Si el formulario es valido
    if (this.form.valid) {
      console.log(this.form.value)

      try {
        // Armamos el objeto que se manda al backend
        const data = {
          name: this.form.value.name,
          password: this.form.value.password,
          phone: this.form.value.phone,
          email: this.form.value.email
        }

        // Mandamos la peticion a la API
        const response: any = await this._provider.request('POST', 'auth/signup', data)

        // Si el backend responde bien, avisamos y mandamos al login
        if (response && !response.error) {
          this._snackBar.open('Cuenta creada con exito. Inicia sesion.', 'Cerrar', { duration: 4000 })
          this.goToSignIn()
        } else {
          // Si el backend manda error, mostramos mensaje
          this._snackBar.open('Error al registrar. Intenta con otro nombre.', 'Cerrar', { duration: 3000 })
        }
      } catch (error) {
        console.error(error)
      }

    } else {
      // Si el formulario tiene errores, los marcamos
      this.form.markAllAsTouched()
    }
  }
}
