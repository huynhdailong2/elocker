require('dotenv').config();

const cluster_id = 1;
const jwt = require('jsonwebtoken');
var token = jwt.sign({'cluster_id': cluster_id}, process.env.SECRET_KEY);

var io = require('socket.io-client')('http://18.139.169.50:8889', {
    query: {token}
});
io.on('connect',()=>{
    console.log('Server connected')
    io.emit('message', {'cluster_id': cluster_id});
});
io.on('connect_error', err => console.log(err))
io.on('connect_failed', err => console.log(err))
io.on('disconnect', () => {
    console.log('Server disconnected!')
})