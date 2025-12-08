import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { LocalstorageService } from './localstorage.service';
import { MatSnackBar } from '@angular/material/snack-bar';

@Injectable({
  providedIn: 'root',
})
export class ProviderService {

  // Inyeccion de dependencias necesarias
  private _http: HttpClient = inject(HttpClient);
  private _localstorage: LocalstorageService = inject(LocalstorageService);
  private _snackBar: MatSnackBar = inject(MatSnackBar);
  
  // Lista de excepciones que mapea codigos del backend a mensajes mas claros
  excep: any = {
    '001': 'Metodo de peticion incorrecto',
    '002': 'Clase incorrecta',
    '003': 'Metodo inexistente',
    '006': 'Token no enviado',
    '007': 'Parametros vacios',
    // Login
    '004': 'El usuario no existe',
    '005': 'Credenciales invalidas',
  };

  // Metodo principal para hacer solicitudes HTTP
  // Recibe:
  // method: tipo de peticion (GET, POST, PUT, DELETE)
  // action: endpoint a donde se hara la peticion
  // data: informacion opcional que se envia con la peticion
  async request<T>(method: string, action: string, data?: any) {

    // Se obtiene el protocolo y el dominio de forma segura
    const PROTOCOL = typeof window !== 'undefined' ? window.location.protocol : 'http:';
    const DOMINIO = typeof window !== 'undefined' ? window.location.hostname : 'localhost';
    
    // URL base de tu backend en PHP
    let url = PROTOCOL + '//' + DOMINIO + '/ws-php/public/';

    // Se retorna una promesa que se resuelve cuando el backend responde correctamente
    return new Promise<T>((resolve, reject) =>
      this._http
        .request<any>(method, url + action, {
          // El cuerpo solo se envia si la peticion no es GET
          body: method !== 'GET' ? data : null,

          // Headers necesarios (con token si existe)
          headers: this.headers(),

          // En GET o DELETE, si data existe, se manda como params
          params: (method === 'GET' || method === 'DELETE') && data ? this.params(data) : {}
        })
        .subscribe({
          // next: el backend respondio con status correcto
          next: (response: any) => {

            // Si el backend marca error = false, todo salio bien
            if (!response.error) {
                resolve(response.msg);
            } else {
                // Hubo un error logico del backend (no es error de red)
                const errorMsg = this.excep[response.error_code] || 'Error desconocido';
                this._snackBar.open(errorMsg, 'Cerrar', { duration: 3000 });
                reject(response.msg);
            }
          },

          // error: cuando hay error de red, parseo o status inesperado
          error: (err) => {
            console.error('Error en la peticion:', err);
            this._snackBar.open('Error de comunicacion con el servidor', 'Cerrar', { duration: 3000 });
            reject(err);
          }
        })
    );
  }

  // Headers que se envian en cada peticion
  // simple: llave estatica
  // authorization: token del usuario si existe
  headers() {
    return new HttpHeaders()
      .set('simple','bb1557a2774351913c8557251ec2cbb4')
      .set('authorization', this._localstorage.getItem('user') == null ? '' : this._localstorage.getItem('user').token);
  }

  // Convierte los parametros en JSON antes de mandarlos en la URL
  params(params: any) {
    if (!params) return {};
    return new HttpParams().set('params', JSON.stringify(params));
  }

}
