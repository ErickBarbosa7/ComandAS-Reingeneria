import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { LocalstorageService } from './localstorage.service';
import { MatSnackBar } from '@angular/material/snack-bar';

@Injectable({
  providedIn: 'root',
})
export class ProviderService {

  private _http: HttpClient = inject(HttpClient);
  private _localstorage: LocalstorageService = inject(LocalstorageService);
  private _snackBar: MatSnackBar = inject(MatSnackBar);
  
  excep: any = {
    '001': 'Metodo de peticion incorrecto',
    '002': 'Clase incorrecta',
    '003': 'Metodo inexistente',
    '006': 'Token no enviado',
    '007': 'Parametros vacios',
    '004': 'El usuario no existe',
    '005': 'Credenciales invalidas',
  };

  async request<T>(method: string, action: string, data?: any) {

    // CAMBIO IMPORTANTE: Apuntar al puerto 8000 donde corre PHP -S
    const PROTOCOL = typeof window !== 'undefined' ? window.location.protocol : 'http:';
    const DOMINIO = typeof window !== 'undefined' ? window.location.hostname : 'localhost';
    const PORT = '8000';
    
    let url = `${PROTOCOL}//${DOMINIO}:${PORT}/`; 

    return new Promise<T>((resolve, reject) =>
      this._http
        .request<any>(method, url + action, {
          body: method !== 'GET' ? data : null,
          headers: this.headers(), // <--- Aquí llamamos a la función corregida
          params: (method === 'GET' || method === 'DELETE') && data ? this.params(data) : {}
        })
        .subscribe({
          next: (response: any) => {
            if (!response.error) {
              resolve(response.msg);
            } else {
              const errorMsg = this.excep[response.error_code] || response.msg || 'Error desconocido';
              this._snackBar.open(errorMsg, 'Cerrar', { duration: 3000 });
              reject(response);
            }
          },
          error: (err) => {
            console.error('Error en petición:', err);
            this._snackBar.open('Error de conexión o servidor', 'Cerrar', { duration: 3000 });
            reject(err);
          }
        })
    );
  }

  // --- FUNCIÓN CORREGIDA ---
  headers() {
    let token = '';
    const user = this._localstorage.getItem('user');

    // Validación segura: Verificamos que user exista Y que tenga la propiedad token
    if (user && user.token) {
        token = user.token;
    }

    return new HttpHeaders()
      .set('simple','bb1557a2774351913c8557251ec2cbb4')
      .set('authorization', token); // Ahora 'token' siempre es un string, nunca undefined
  }

  params(params: any) {
    if (!params) return {};
    return new HttpParams().set('params', JSON.stringify(params));
  }
}