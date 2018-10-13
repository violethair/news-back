var config = require('./config.json');
var mysql = require('promise-mysql');

async function run () {
	try {
		var conn = await mysql.createConnection(config);
	} catch (e) {
		return console.log('\x1b[31m%s\x1b[0m', "Connect Error:" + e.message);
	}

	console.log('\x1b[32m%s\x1b[0m', "Connect Successfully");

	// get post avaiable
	var rows = await conn.query("SELECT * FROM posts WHERE status='pending' AND publish_schedule >= '"+getNowFormat()+"'");

	if(rows.length < 0) {
		setTimeout(function () {
			run();
		},5 * 60 * 1000) // 5 minutes
		return;
	}

	for (var i = 0; i < rows.length; i++) {
		await conn.query("UPDATE posts SET status='publish' WHERE id=" + rows[i].id);
		console.log('\x1b[32m%s\x1b[0m', "PUBLISHED POST:" + rows[i].id);
	}

	setTimeout(function () {
		run();
	},5 * 60 * 1000) // 5 minutes
}

function formatNumber(number) {
	if(number < 10) return "0" + number;
	return number;
}

function getNowFormat () {
	var now = new Date();
	return now.getFullYear() + "-"+formatNumber(now.getMonth() + 1)+"-"+formatNumber(now.getDate())+" "+formatNumber(now.getHours())+":"+formatNumber(now.getMinutes())+":" + formatNumber(now.getSeconds());
}

run();