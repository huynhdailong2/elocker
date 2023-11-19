require('dotenv').config();
const jwt = require('jsonwebtoken');

/**
 * Module dependencies.
 */
const http = require('http');

/**
 * Get port from environment and store in Express.
 */

const port = normalizePort(process.env.NODE_PORT || '8889');

/**
 * Create HTTP server.
 */

const server = http.createServer();


/**
 * Connect to mariadb
 */

const mariadb = require('mariadb');
const pool = mariadb.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE
});

function setOnlineOffline(id, val) {
    pool.getConnection()
        .then(conn => {
            conn.query('UPDATE clusters SET is_online = ?, updated_at = NOW() WHERE id = ?', [val, id])
                .then(rows => {
                    console.log(rows);
                    conn.end();
                })
                .catch(err => {
                    console.log(err);
                    conn.end();
                });
        })
        .catch(err => {
            console.log(err);
        });
}

/**
 * Connect socket.io
 */
const options = { allowEIO3: true, path: '/health' };
const io = require("socket.io")(server, options);
/**
 * Listen on provided port, on all network interfaces.
 */

server.listen(port);
server.on('error', onError);

/**
 * Normalize a port into a number, string, or false.
 */

function normalizePort(val) {
    let port = parseInt(val, 10);

    if (isNaN(port)) {
        // named pipe
        return val;
    }

    if (port >= 0) {
        // port number
        return port;
    }

    return false;
}

/**
 * Event listener for HTTP server "error" event.
 */

function onError(error) {
    if (error.syscall !== 'listen') {
        throw error;
    }

    var bind = typeof port === 'string'
        ? 'Pipe ' + port
        : 'Port ' + port;

    // handle specific listen errors with friendly messages
    switch (error.code) {
        case 'EACCES':
            console.error(bind + ' requires elevated privileges');
            process.exit(1);
        case 'EADDRINUSE':
            console.error(bind + ' is already in use');
            process.exit(1);
        default:
            throw error;
    }
}



var ids = [];

io.use(function (socket, next) {
    if (socket.handshake.query && socket.handshake.query.token) {
        jwt.verify(socket.handshake.query.token, process.env.SECRET_KEY, function (err, decoded) {
            if (err) return next(new Error('Authentication error'));
            socket.decoded = decoded;
            next();
        });
    }
    else {
        next(new Error('Authentication error'));
    }
})
    .on('connection', function (socket) {
        // Connection now authenticated to receive further events
        
        socket.on('message', function (dt) {
            if (dt.cluster_id) {
                //update db for online
                setOnlineOffline(dt.cluster_id, 1);
                ids[socket.id] = dt.cluster_id
            }
            // io.emit('message', message);
        });
        socket.on('disconnect', () => {
            console.log('Client disconnected!')
            let id = ids[socket.id];
            //update db for offline
            setOnlineOffline(id, 0);
            delete ids[socket.id];
        });
    });