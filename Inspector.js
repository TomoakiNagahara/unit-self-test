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
			root.className = 'root';
		var list = document.createElement('ol');
			list.classList.add('root');

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
				li.dataset.user   = user_name;
				li.dataset.result = result;
				li.appendChild(span);
			list.appendChild(li);
		}
	}

	//	...
	function __user(list, json){
		for(var user_name in json){
			//	...
			var root = __search_root(list);
			var node = list.querySelector('li[data-user="' + user_name + '"]');
			var result = node.dataset.result;

			//	...
			if( result !== "true" ){
				console.log(user_name, result);
				continue;
			}

			//	...
			var table_list = document.createElement('ol');
			node.appendChild(table_list);

			//	...
			__tables(table_list, json[user_name]['tables'][user_name]);

			//	..
			__fields(table_list, json[user_name]['fields'][user_name]);
		}
	}

	//	...
	function __tables(list, json){
		//	...
		for(var table_name in json){
			var result = json[table_name];

			//	...
			var span = document.createElement('span');
				span.innerText = table_name;

			//	...
			var li   = document.createElement('li');
				li.dataset.table  = table_name;
				li.dataset.result = result;
				li.appendChild(span);

			//	...
			list.appendChild(li);
		}
	}

	//	...
	function __fields(list, json){
		//	...
		for(var table_name in json){
			var target = list.querySelector('li[data-table="' + table_name + '"]');
			var result = target.dataset.result;
			if( result === "false" ){
				continue;
			}
			console.log(table_name, result);

			//	...
			var field_list = document.createElement('ol');
			target.appendChild(field_list);

			//	...
			__field(field_list, json[table_name]);
		}
	}

	//	...
	function __field(list, json){
		console.log(list, json);
		for(var field_name in json){
			var result = json[field_name];

			//	...
			var span = document.createElement('span');
				span.innerText = field_name;

			//	...
			var li   = document.createElement('li');
				li.dataset.result = result;
				li.appendChild(span);

			//	...
			list.appendChild(li);
		}
	}

	//	...
	function __search_root(node){
		//	...
		var parent_node = node.parentNode;
		var tag_name    = parent_node.tagName;
		var result_node = null;

		//	...
		switch( tag_name ){
			case 'OL':
				var class_name = parent_node.className;
				if( class_name === 'root' ){
					result_node = parent_node;
				}else{
					console.log( tag_name, class_name );
				}
				break;
			default:
				result_node = __search_root(parent_node);
		}

		return result_node;
	}
};

//	...
window.addEventListener("load", function(e){
	setTimeout(OP.Selftest.Inspection, 0);
});