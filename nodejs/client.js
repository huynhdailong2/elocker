require('dotenv').config();

const cluster_id = 1;
const jwt = require('jsonwebtoken');
var token = jwt.sign({'cluster_id': cluster_id}, process.env.SECRET_KEY);

var io = require('socket.io-client')('http://elocker-test.drk-system.com', {
    path: '/health',
    query: {token}
});
io.on('connect',()=>{
    console.log('Server connected')
    io.emit('message', {'cluster_id': cluster_id});
});
io.on('connect_error', err => console.log('[connect_error]', err))
io.on('connect_failed', err => console.log('[connect_failed]', err))
io.on('disconnect', () => {
    console.log('Server disconnected!')
})