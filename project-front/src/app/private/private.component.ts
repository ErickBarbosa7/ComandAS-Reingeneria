
import { Component, inject } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import {MatSidenavModule} from '@angular/material/sidenav';
import { MenuComponent } from './menu/menu.component';
import {MatFormFieldModule } from '@angular/material/form-field';
import { Router, RouterLink, RouterOutlet } from '@angular/router';
import { LocalstorageService } from '../services/localstorage.service';

@Component({
  selector: 'app-private',
  standalone: true,
  imports: [MatSidenavModule, MatIconModule, MenuComponent, MatFormFieldModule,RouterOutlet,RouterLink],
  templateUrl: './private.component.html',
  styleUrl: './private.component.scss'
})
export class PrivateComponent {
  private _localstorage: LocalstorageService = inject(LocalstorageService)
  private _router: Router = inject(Router)

  user: any = {}
  rol: number = 0

  ngOnInit() {

    // Se suscribe a los cambios del usuario en el localstorage (BehaviorSubject)
    this._localstorage.user$.subscribe((data) => {
      if (data) {
        this.user = data
        this.rol = Number(this.user.rol)
        console.log('Usuario actualizado:', this.user)
      }
    })

    // Si ya existia un usuario guardado, lo toma como inicial
    const current = this._localstorage.getItem('user')
    if (current) {
      this.user = current
      this.rol = Number(this.user.rol)
    }
  }

  // Cierra sesion, limpia localstorage y regresa al login
  logOut() {
    this._localstorage.clear()
    this._router.navigate(['/auth/sign-in'])
  }
}