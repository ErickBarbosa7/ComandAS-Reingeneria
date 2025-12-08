import { Component, inject, ViewChild } from '@angular/core';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIcon } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatPaginatorModule, MatPaginator } from '@angular/material/paginator';
import { MatSortModule, MatSort } from '@angular/material/sort';
import { MatTableModule, MatTableDataSource } from '@angular/material/table';
import { RouterLink } from '@angular/router';
import { ProviderService } from '../../services/provider.service';

@Component({
  selector: 'app-user-view',
  standalone: true,
  imports: [MatFormFieldModule, MatInputModule, MatTableModule, MatSortModule, MatPaginatorModule, RouterLink, MatIcon],
  templateUrl: './user-view.component.html',
  styleUrl: './user-view.component.scss'
})
export class UserViewComponent {

  // Columnas que se muestran en la tabla
  displayedColumns: string[] = ['name', 'email', 'phone', 'rol']

  // Fuente de datos de la tabla
  dataSource!: MatTableDataSource<any>

  // Servicio para obtener los usuarios
  private _provider: ProviderService = inject(ProviderService)

  // Referencias al paginador y ordenamiento
  @ViewChild(MatPaginator) paginator!: MatPaginator
  @ViewChild(MatSort) sort!: MatSort

  // Lista de roles para mostrar en texto
  roles = [
    { name: 'Administrador', value: 0 },
    { name: 'Cajero', value: 1 },
    { name: 'Cocinero', value: 2 },
    { name: 'Cliente', value: 3 },
  ]

  // Se ejecuta cuando la vista ya esta cargada
  async ngAfterViewInit() {

    // Pide los usuarios al backend
    var users: any[] = await this._provider.request('GET', 'user/viewUsers')
    console.log(users)

    // Crea la tabla con el resultado
    this.dataSource = new MatTableDataSource(users)

    // Conecta paginador y ordenamiento
    this.dataSource.paginator = this.paginator
    this.dataSource.sort = this.sort
  }

  // Filtro para buscar en la tabla
  applyFilter(event: Event) {
    const filterValue = (event.target as HTMLInputElement).value
    this.dataSource.filter = filterValue.trim().toLowerCase()

    // Regresa a la primera pagina si hay paginador
    if (this.dataSource.paginator) {
      this.dataSource.paginator.firstPage()
    }
  }

  // Convierte el numero de rol al nombre que se muestra
  mapRol(id: number) {
    return this.roles.find((rol: any) => rol.value == id)!.name
  }
}
