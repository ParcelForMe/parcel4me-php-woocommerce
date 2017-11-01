var Express 		= require('express');
var Http			= require('http');
var BodyParser		= require('body-parser');

var p4mEndpoint 	= require('./p4m_endpoints.js');


var SITE_PORT = process.env.port || 8080;


//--- Helper middleware functions
function allowCrossDomain(req, res, next) {
	res.header('Access-Control-Allow-Origin', '*');
	res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS,PATCH,FILE');
	res.header('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
	next();
};

function addJsonHeaders(req, res, next) {
	res.header('Content-Type', 'application/json');
	next();
};

function simulateDelay(req, res, next) {
	console.log('Waiting for 3 seconds..');
	setTimeout(next, 3000);
}

function logRoute(req, res, next) {
	var fullUrl = /*'req.protocol + '://'*/ ' ' + req.get('host') + req.originalUrl;
	console.log(fullUrl);
	next();
}



//--- Set up site and API servers
var site = Express();

site.use(BodyParser.json());
site.use(allowCrossDomain);
site.set('port', SITE_PORT);



//--- API Routing

site.use(logRoute);


/* 

These are the minimum parcel 4 me endpoints we need to implement so 
that the widgets will actually work

*/
site.use('/p4m/getP4MAccessToken', p4mEndpoint.getP4MAccessToken); 
site.use('/p4m/localLogin', p4mEndpoint.localLogin); 
site.use('/p4m/checkout', p4mEndpoint.checkout);
site.post('/p4m/updShippingService', p4mEndpoint.updShippingService);
site.post('/p4m/itemQtyChanged', p4mEndpoint.itemQtyChanged);

// TODO : check if discount code is valid or not ("AAA" is valid !)
site.use('/p4m/applyDiscountCode', simulateDelay); // and then continue on to static file

/*

At this stage all other endpoints are handled by a static file (per endpoint)
Hard-coded sample data

*/
site.use('/p4m', addJsonHeaders);
site.use('/p4m', Express.static('static_api/p4m')); 




//--- Routing for index.html and widgets 
site.use('/lib/p4m-widgets', Express.static('..'));
site.use('/lib', Express.static('../bower_components'));
site.use('/build', Express.static('../build'));
site.use(Express.static('.'));


//--- Start server
Http.createServer(site).listen(SITE_PORT);
console.log("Server listening on", SITE_PORT);

//--- Exception safety net
process.on('uncaughtException', function(err) {
	console.log("Unhandled exception:", err.stack);
});
