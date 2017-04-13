/**
 * unit-self-test:/Inspector.js
 *
 * @creation  2017-04-12
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
//	...
if( !OP ){
	var OP = {};
}

//	...
if(!OP.Selftest ){
	OP.Selftest = {};
}

//	...
OP.Selftest.Inspection = function(){
	var inspections = document.getElementsByClassName('inspection');
	for(var i=0; i<inspections.length; i++){
		var inspection = inspections[i];
		var json = JSON.parse(inspection.innerText);
		var root = __root(json);

		//	...
		inspection.innerText = '';
		inspection.appendChild(root);
	}

	//	...
	function __root(json){
	//	console.dir(json);

		//	...
		var root = document.createElement('div');
		var list = document.createElement('ol');
			list.className = 'root';

		//	...
		root.appendChild(list);

		//	...
		__host(list, json);

		//	...
		return root;
	}

	//	...
	function __host(list, json){
		var p = document.createElement('p');
			p.className = 'label';
			p.innerText = 'Host names';
		list.parentNode.insertBefore(p, list);

		//	...
		for(var hostname in json){
			//	...
			var li = document.createElement('li');
				li.innerText = hostname;
			list.appendChild(li);

			//	...
			var list = document.createElement('ul');
			li.appendChild(list);

			//	...
			__product(list, json[hostname]);
		}
	}


	//	...
	function __product(list, json){
		var p = document.createElement('p');
			p.className = 'label';
			p.innerText = 'Products';
		list.parentNode.insertBefore(p, list);

		for(var product in json){
			//	...
			var li = document.createElement('li');
				li.innerText = product;
			list.appendChild(li);

			//	...
			var list = document.createElement('ul');
			li.appendChild(list);

			//	...
			__port(list, json[product]);
		}
	}

	//	...
	function __port(list, json){
		var p = document.createElement('p');
			p.className = 'label';
			p.innerText = 'Ports';
		list.parentNode.insertBefore(p, list);

		for(var port in json){
			//	...
			var li = document.createElement('li');
				li.innerText = port;
			list.appendChild(li);

			//	...
			var list = document.createElement('ul');
			li.appendChild(list);

			//	...
			__users(list, json[port]['users']);

			//	...
			__user(list, json[port]['user']);
		}
	}

	//	...
	function __users(list, json){
		var p = document.createElement('p');
			p.className = 'label';
			p.innerText = 'Users';
		list.parentNode.insertBefore(p, list);

		for(var user_name in json){
			var result = json[user_name] ? 'true':'false';

			//	...
			var span = document.createElement('span');
				span.innerText = user_name;

			//	...
			var li = document.createElement('li');
				li.dataset.result = result;
				li.appendChild(span);
			list.appendChild(li);
		}
	}

	//	...
	function __user(list, json){
		for(var user_name in json){
			console.log(user_name);
		}
	}
};

//	...
window.addEventListener("load", function(e){
	setTimeout(OP.Selftest.Inspection, 0);
});