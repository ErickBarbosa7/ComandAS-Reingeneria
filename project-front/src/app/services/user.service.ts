import { Injectable, inject } from '@angular/core';
import { ProviderService } from './provider.service';

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private _provider: ProviderService = inject(ProviderService);

  // Obtener lista de empleados (Backend ya filtra rol != 3 si lo configuraste así, o trae todos)
  async getUsers() {
    return await this._provider.request('GET', 'user/viewUsers');
  }

  // Crear usuario (Staff)
  async createUser(data: any) {
    return await this._provider.request('POST', 'user/create', data);
  }

  // Actualizar usuario
  async updateUser(data: any) {
    // El backend espera 'idusers' o 'id'
    return await this._provider.request('POST', 'user/update', data);
  }

  // Eliminar usuario
  async deleteUser(id: string) {
    return await this._provider.request('DELETE', 'user/delete', { idusers: id });
  }
}