import Server from './classes/server';
import router from './routes/router';
import bodyParser from 'body-parser';
import cors from 'cors';
import { RequestHandler } from 'express';

const server = Server.instance;

// BodyParser
server.app.use(bodyParser.urlencoded({ extended: true }) as RequestHandler);
server.app.use(bodyParser.json() as RequestHandler);
// CORS
server.app.use(cors({ origin: true, credentials: true }));

// Rutas de servicios
server.app.use('/socket', router);
//server.app.use('/', router);

server.start(() => {
    console.log(`Servidor corriendo en el puerto ${server.port}`);
});
