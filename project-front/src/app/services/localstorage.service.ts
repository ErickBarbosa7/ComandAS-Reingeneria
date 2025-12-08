import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LocalstorageService {

  // BehaviorSubject para mantener y emitir el estado del usuario en tiempo real
  private userSource = new BehaviorSubject<any>(null);

  // Observable que otros componentes pueden suscribirse para recibir cambios del usuario
  public user$ = this.userSource.asObservable();

  // Guarda un valor en localStorage
  // Si la clave es "user", también actualiza el BehaviorSubject para notificar cambios
  public async setItem(key: string, data: any) {

    // Guarda el valor en localStorage convertido a JSON
    localStorage.setItem(key, JSON.stringify(data));

    // Si el valor que se guarda es el usuario, emitir el cambio
    if (key === 'user') {
      this.userSource.next(data);
    }
  }

  // Obtiene un valor desde localStorage
  // Si no existe, devuelve null
  public getItem(key: string) {

    // Obtiene el valor, si no existe devuelve 'null' y se parsea a null
    return JSON.parse(localStorage.getItem(key) || 'null');
  }

  // Limpia completamente el localStorage
  // y resetea el BehaviorSubject del usuario
  public clear() {
    localStorage.clear();     // Borra todo el almacenamiento local
    this.userSource.next(null); // Notifica que ya no hay usuario activo
  }
}
