/*

The endpoints have been implemented in a minimal way, to allow
the widgets to function - this is NOT a full P4M implementation

*/

var Express = require('express');

var Cookies     	= require('cookies');
var request 		= require('request');
var fs				= require('fs');


exports.getP4MAccessToken = function(req, res) {
	
	var cookies = new Cookies( req, res );

	if (cookies.get('p4mState') != req.query.state) {
	    res.status(500).send('Authentication error (p4mState)');
	}


	var url = "https://dev.parcelfor.me:44333/connect/token";

	var data = {
		grant_type		: "authorization_code",
		code			: req.query.code,
		redirect_uri	: "http://localhost:8080/p4m/getP4MAccessToken",
		client_id		: "10006"
	}

	var options = {
		url: url,
		headers: {
			"Authorization": "Basic " + new Buffer("10006:secret").toString('base64'),
			"Content-Type": "application/x-www-form-urlencoded"
		},
		form : data
	}


	request.post(options, function(error, response, body) {

		var now = new Date();
		var cookieConf = { path : '/', expires: new Date(now.setFullYear(now.getFullYear() + 1)) , httpOnly: false };

		cookies.set('p4mToken', JSON.parse(body).access_token, cookieConf);

		res.status(200).send('<script>window.close();</script>');
		res.end(); // needed for cookies to save !
	});


};


exports.localLogin = function(req, res) {

	var cookies = new Cookies( req, res );

	// see these cookies that are used by the other p4m widgets
	var now = new Date();
	var cookieConf = { path : '/', expires: new Date(now.setFullYear(now.getFullYear() + 1)) , httpOnly: false };
	cookies.set('p4mAvatarUrl', 		'http://localhost:8080/profile.png', cookieConf);
	cookies.set('p4mGivenName', 		'Hugo', cookieConf);
	cookies.set('p4mDefaultPostCode', 	'4000', cookieConf);
	cookies.set('p4mDefaultCountryCode', 'AU', cookieConf);
	cookies.set('p4mOfferCartRestore', 	'true', cookieConf);
	cookies.set('p4mLocalLogin', 		'true', cookieConf); 

	res.status(200).json({ "RedirectUrl": null, "Success": true, "Error": null});
	res.end(); // needed for cookies to save !
};


exports.checkout = function(req, res) {

	var cookies = new Cookies( req, res );

	if (true)
	//if ( (cookies.get('gfsCheckoutToken')==null) || (cookies.get('gfsCheckoutToken')=='') ) 
	{
	    
		var url = "https://identity.justshoutgfs.com/connect/token";

		var data = {
			grant_type	: "client_credentials",
			scope 		: "read checkout-api"
		}

		var options = {
			url: url,
			headers: {
				"Authorization": "Basic " + new Buffer("parcel_4_me:needmoreparcels").toString('base64'),
				"Content-Type": "application/x-www-form-urlencoded"
			},
			form : data
		}

		request.post(options, function(error, response, body) {

			var now = new Date();
			var cookieConf = { path : '/', expires: new Date(now.setFullYear(now.getFullYear() + 1)) , httpOnly: false };
			var base64Token = new Buffer(JSON.parse(body).access_token).toString('base64');
			cookies.set('gfsCheckoutToken', base64Token, cookieConf);

			returnTemplateFile('checkout.html', '[gfs-access-token]', base64Token, res);
			
		});
	} 
	else {	
		console.log('THIS NEVER HAPPENS CURRENTLY.. but if i stored the gfs-access-token somewhere then I could do this logic ..');
		returnTemplateFile('checkout.html', '[gfs-access-token]', base64Token, res);
	}
}


exports.itemQtyChanged = function(req, res) {
    var result = {
        Success: true
    };
    res.status(200).json(result);
}

function returnTemplateFile(file, find, replace, res) {

	fs.readFile('static_api/templates/'+file, function read(err, file_contents) {
		if (err) {
			res.status(500).send(err);
		}
		// IMPLEMENTING ONLY THE MOST BASIC FIND REPLACE, OF ONE SHORT CODE AND ONLY ONE OCCURANCE
		file_contents = file_contents.toString('utf8').replace(find, replace);  

		res.set('Content-Type', 'text/html');
		res.status(200).send(file_contents);
		res.end(); // needed for cookies to save !
	});
}

exports.updShippingService = function(req, res) {
    var result = {
        Discount: 0,
        Error: null,
        Shipping: req.body.Amount,
        Success: true
    };
    try {
    result.Tax = Math.floor(result.Shipping / 10);
    result.Total = result.Tax + result.Shipping;
    }
    catch(e) {
    	console.error(e);
    	result.Tax = 0;
    	result.Total = result.Shipping;
    }
    res.status(200).json(result);
}