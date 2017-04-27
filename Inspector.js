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

		//	...
		setTimeout(function(){ __finished(root); }, 1000);
	}

	//	...
	function __root(json){
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
		//	Each user.
		for(var user_name in json){
			//	...
			var root = __search_root(list);
			var node = list.querySelector(`li[data-user="${user_name}"]`);
			var result = node.dataset.result;

			//	Check to each user connection.
			if( result !== "true" ){
				console.log('Database connection: ', user_name, result);
				continue;
			}

			//	...
			__databases(node, json[user_name]['databases']);

			//	...
			__tables(node, json[user_name]['tables']);

			//	...
			__fields(node, json[user_name]['fields']);

			//	...
			__structs(node, json[user_name]['structs']);
		}
	}

	//	...
	function __databases(list, json){
		if( json === undefined ){
			return;
		}

		//	...
		var ol = document.createElement('ol');
		list.appendChild(ol);

		//	...
		for(var db_name in json){
			//	...
			var result = json[db_name];

			//	...
			var li = document.createElement('li');
				li.dataset.database = db_name;
				li.dataset.result   = result;
			ol.appendChild(li);

			//	...
			var span = document.createElement('span');
				span.innerText = db_name;
				li.appendChild(span);
		}
	}

	//	...
	function __tables(list, json){
		if( json === undefined ){
			return;
		}

		//	...
		for(var db_name in json){
			//	...
			var node = list.querySelector(`li[data-database="${db_name}"]`);

			//	...
			var ol = document.createElement('ol');
			node.appendChild(ol);

			//	...
			for(var table_name in json[db_name]){
				var result = json[db_name][table_name];

				//	...
				var span = document.createElement('span');
					span.innerText = table_name;

				//	...
				var li = document.createElement('li');
					li.dataset.table  = table_name;
					li.dataset.result = result;
					li.appendChild(span);
				ol.appendChild(li);
			}
		}
	}

	//	...
	function __fields(list, json){
		if( json === undefined ){
			return;
		}

		//	...
		for(var db_name in json){
			for(var table_name in json[db_name]){
				//	...
				var node = list.querySelector(`li[data-database="${db_name}"] li[data-table="${table_name}"]`);

				//	...
				var ol = document.createElement('ol');
				node.appendChild(ol);

				//	...
				for(var field_name in json[db_name][table_name]){
					var result = json[db_name][table_name][field_name];

					//	...
					var span = document.createElement('span');
						span.innerText = field_name;

					//	...
					var li = document.createElement('li');
						li.dataset.field  = field_name;
						li.dataset.result = result;
						li.appendChild(span);
					ol.appendChild(li);
				}
			}
		}
	}

	//	...
	function __structs(list, json){
		if( json === undefined ){
			return;
		}

		//	...
		for(var db_name in json){
			for(var table_name in json[db_name]){
				for(var field_name in json[db_name][table_name]){
					//	...
					var node = list.querySelector(`li[data-database="${db_name}"] li[data-table="${table_name}"] li[data-field="${field_name}"]`);

					//	...
					var ol = document.createElement('ol');
					node.appendChild(ol);

					//	...
					for(var parameter in json[db_name][table_name][field_name]){
						//	...
						var result = json[db_name][table_name][field_name][parameter];

						//	...
						var span = document.createElement('span');
							span.innerText = parameter;

						//	...
						var li = document.createElement('li');
							li.dataset.parameter = parameter;
							li.dataset.result    = result;
							li.appendChild(span);
						ol.appendChild(li);

						if( !result ){
							console.log(db_name, table_name, field_name, parameter, result);
						}
					}
				}
			}
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

	//	...
	function __finished(root){
		//	...
		var nodes = root.querySelectorAll('li[data-result="false"]');
		for(var i in nodes){
			__finished_node(nodes[i]);
		}

		//	...
		var nodes = root.querySelectorAll('li[data-result="true"]');
		for(var i=0; i<nodes.length; i++){
			var node = nodes[i];
			var span = node.querySelector('span');
				span.className = 'fadeout';
			//	node.className = 'fadeout';
		}
	}

	//	...
	function __finished_node(node){
		if( !node.parentNode || node.parentNode.tagName === 'DIV' ){
			return;
		}

		var parent_node = node.parentNode.parentNode;
		var tag_name    = parent_node.tagName;
		if( tag_name === 'LI' ){
			parent_node.dataset.result = false;
			__finished_node(parent_node);
		}
	}
};

//	...
window.addEventListener("load", function(e){
	setTimeout(OP.Selftest.Inspection, 0);
});