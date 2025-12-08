import { Component, inject } from '@angular/core';
import { MatTabsModule } from '@angular/material/tabs';
import { ProviderService } from '../../services/provider.service';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatTableModule } from '@angular/material/table';
import { OrderDetailComponent } from '../order-detail/order-detail.component';
import { DialogCompleteComponent } from '../dialog-complete/dialog-complete.component';
import { DialogCancelComponent } from '../dialog-cancel/dialog-cancel.component';
import { WebSocketsService } from '../../services/web-sockets.service';
import { LocalstorageService } from '../../services/localstorage.service';

@Component({
  selector: 'app-orders-view',
  standalone: true,
  imports: [MatTabsModule, MatDialogModule, MatTableModule],
  templateUrl: './orders-view.component.html',
  styleUrl: './orders-view.component.scss'
})
export class OrdersViewComponent {

  // servicios que se usan en este componente
  private _provider: ProviderService = inject(ProviderService);
  private dialog: MatDialog = inject(MatDialog);
  private _wsService: WebSocketsService = inject(WebSocketsService);
  private _storage: LocalstorageService = inject(LocalstorageService);
  
  // lista de ordenes que se recibe desde la API
  order: any[] = []; 

  // info del usuario logueado
  currentUser: any = {};
  userRol: number = 0;   // rol del usuario en numero para controlar la vista

  // lista de estados que se muestran en tabs
  status = [
    { name: "Registradas", value: 0 },
    { name: "En cocina", value: 1 },
    { name: "Orden lista", value: 2 },
    { name: "Completadas", value: 3 },
    { name: "Canceladas", value: 4 }
  ];

  // columnas que usa la tabla
  displayedColumns = ['client', 'total', 'comments', 'function'];

  // carga inicial
  async ngOnInit() {
    // toma el usuario guardado en localstorage
    this.currentUser = this._storage.getItem('user');
    this.userRol = Number(this.currentUser.rol);
    
    // si el usuario es cliente solo ve sus ordenes
    let params = {};
    if (this.userRol === 3) {
       params = { id_user: this.currentUser.idusers };
    } 

    try {
        // hace la peticion a la API
        const res: any = await this._provider.request('GET', 'order/viewOrders', params);

        // valida la estructura de la respuesta antes de asignarla
        if (res && res.msg && Array.isArray(res.msg)) {
            this.order = res.msg;
        } else if (Array.isArray(res)) {
            this.order = res;
        } else {
            this.order = [];
        }
    } catch (error) {
        console.error("Error cargando ordenes:", error);
    }

    // activa la escucha de sockets para actualizar pedidos en tiempo real
    this.listenSocket();
  }

  // devuelve solo las ordenes que coinciden con el estado seleccionado
  filterByStatus(status: number) {
    if (!this.order) return [];
    return this.order.filter((eachOrder: any) => eachOrder.status == status);
  }

  // abre el modal con los detalles de la orden
  openOrderDetailDialog(idorder: string) {
    this.dialog.open(OrderDetailComponent, { data: idorder });
  }

  // abre el modal para marcar una orden como completada
  openConfirmDialog(data: string) {
    this.dialog.open(DialogCompleteComponent, { data: data });
  }

  // abre el modal para cancelar una orden
  openCancelDialog(data: any) {
    this.dialog.open(DialogCancelComponent, { data: data });
  }

  // escucha cambios por sockets para actualizar en vivo
  listenSocket() {
    this._wsService.listen('comanda').subscribe((data: any) => {
      console.log('Socket recibido:', data);

      // si es cliente solo actualiza si la orden es suya
      if (this.userRol === 3) {
        const misPedidos = this.order.map(o => String(o.idorder));
        if (!misPedidos.includes(String(data.idorder))) {
          return;
        }
      }

      // actualiza la lista reemplazando la orden modificada
      if (this.order) {
        const ordenesFiltradas = this.order.filter(
          (item) => String(item.idorder) !== String(data.idorder)
        );
        this.order = [data, ...ordenesFiltradas];
      }
    });
  }

}
