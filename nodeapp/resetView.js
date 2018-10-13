var config = require('./config.json');
var mysql = require('promise-mysql');

function checkTime () {
	var date = new Date();
	var hours = date.getHours();
	var minutes = date.getMinutes();

	console.log(hours + ":" + minutes);

	if(hours == 23 && minutes == 20) {
		run(function () {
			setTimeout(function () {
				checkTime();
			},60 * 1000);
		});
	} else {
		setTimeout(function () {
			checkTime();
		},60 * 1000);
	}
}

async function run (callback) {
	try {
		var conn = await mysql.createConnection(config);
	} catch (e) {
		return console.log('\x1b[31m%s\x1b[0m', "Connect Error:" + e.message);
	}

	console.log('\x1b[32m%s\x1b[0m', "Connect Successfully");

	// update all post
	await conn.query("UPDATE posts SET view=0");
	await conn.end();
	console.log('\x1b[32m%s\x1b[0m', "RESET VIEW ALL POST SUCCESSFULLY");
	callback();
}

checkTime();
// run();