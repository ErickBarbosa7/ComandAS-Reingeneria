
import { Router, Request, Response } from 'express';
import Server from '../classes/server';

const router = Router();


router.post('/comandas', (req: Request, res: Response) => {

    const data = req.body;
    const server = Server.instance;

    server.io.emit('comanda',data);

    res.json(true);

});
router.post('/graficas', (req: Request, res: Response) =>{
    const data = req.body;
    const server = Server.instance;

    server.io.emit('grafica',data);

    res.json(true);
});
// Ruta POST para manejar las solicitudes en la ruta '/usuarios'
router.post('/usuarios', (req: Request, res: Response) => {
    // Obtiene los datos del cuerpo de la solicitud
    const data = req.body;
    // Obtiene la instancia del servidor
    const server = Server.instance;
    // Emite los datos a todos los clientes conectados en el canal 'usuario'
    server.io.emit('usuario', data);
    // Responde con un JSON que indica éxito
    res.json(true);
});

export default router;


