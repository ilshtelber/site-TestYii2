<?php 
/**
*для работы этого скрипта, требуеться следующие файлы
*graph.css - для стилизаций вверхней панели кнопок
*fabric.min.js - для canvas, отображение вершин, графов и так далее
*jquery-3.3.1.min.js - Jquery библеотека, через него работает большенство событий
*jquery.cookie.js - плагин для JQuery
*/
?>
<link href="web/css/graph.css" rel="stylesheet">
<section id="graph">
	<div class="container">
		<div class="row_1">
			<select id="graph_list" class="list" size="1">
			</select>
			<button id="create_graph" class="btn">создать граф</button>
			<button id="delete_graph" class="btn">удалить граф</button>
		</div>
		<form id="Edge" class="row_2">
			<input name="from" class="text" type="text" placeholder="from" size="5" maxlength="10">
			<input name="to" class="text" type="text" placeholder="to" size="5" maxlength="10">
			<button id="relate" class="btn">связать вершины</button>
			<button id="dissociate" class="btn">разорвать связи вершин</button>
			<button id="shortest" class="btn">найти кратчайший путь</button>
			<button id="help" class="btn" onclick="return false;">?</button>
		</form>
	</div>
	<div id="weight"></div>
	<canvas id="canvas" width="1100" height="500" style="border: 1px solid black;">

	</canvas>
	<div id="error" style="color: red"></div>
	<div id="tooltip"></div>
	<script>
	(function() {
		var header = {Authorization: 'Bearer eEGuOntqR5FrWiAbxC1CSVP8hhOyCq2d'};
		var remainder_vertex = [];
		var remainder_line = [];
		var id_graph = $.cookie('graph')?$.cookie('graph'):1;
		var back_graph = $.cookie('graph')?[1]:[];
		var SERVER = "http://host1703081.hostland.pro";
		var graph = new Object();
		var canvas = new fabric.Canvas('canvas',{backgroundColor: 'rgb(252,250,250)', selection: false});
		fabric.Object.prototype.originX = fabric.Object.prototype.originY = 'center';

		var Vertex = fabric.util.createClass(fabric.Group, {

			type: 'Vertex',

			initialize: function(label, options) {
				options || (options = { });
				this.label = label;
				this.line_from = new Array();
				this.line_to = new Array();
				this.vertex_from = new Array();
				this.figure = "Vertex";
				var text = new fabric.Text(label, { 
					fill: 'black',
					shadow: 'rgba(0,0,0,0.2) 2px 2px 2px',
					stroke: 'black', 
					strokeWidth: 1,
					fontFamily: 'Calibri', 
					fontSize: 40 - label.length*(10/(1+label.length*0.15)),
					angle: -15,
					originX: 'center',
					originY: 'center'
				});

				var circle = new fabric.Circle({
					fill: 'rgba(126,194,188,0.9)',
					shadow: 'rgba(0,0,0,0.7) 6px 5px 15px',
					stroke: 'rgba(16,133,122,0.4)',
					strokeWidth: 3,
					radius: 15,
					originX: 'center',
					originY: 'center'
				});

				this.callSuper('initialize', [circle, text], options);
				this.set({lockScalingX: true, lockRotation: true, lockScalingY: true, hasControls: false});
				//this.set({hasControls: false, hasBorders: false});
			},

			toObject: function() {
				return fabric.util.object.extend(this.callSuper('toObject'), {label: this.label});
			},
		});


		var Edge = fabric.util.createClass(fabric.Line, {

			type: 'Edge',
			textfill: '#333',

			initialize: function(coord, options) {
				options || (options = { });
				this.callSuper('initialize', coord, options);
				this.set('label', options.label || '');
			},

			toObject: function() {
				return fabric.util.object.extend(this.callSuper('toObject'), {label: this.get('label')});
			},

			_render: function(ctx) {
				this.callSuper('_render', ctx);
				if(this.sided === false){
					ctx.fillStyle = 'rgb(252,250,250)';
					ctx.fillRect(-6 - (5*this.label.length),-12,11+(10*this.label.length),23);
					ctx.font = "20px sans-serif";
					ctx.fillStyle = this.textfill;
					ctx.fillText(this.label, -this.label.length*6, 7);
				}

				if(this.sided === true){
					ctx.fillStyle = 'rgb(252,250,250)';
					ctx.fillRect(-6 - (5*this.label.length),6,11+(10*this.label.length),23);
					ctx.font = "20px sans-serif";
					ctx.fillStyle = this.textfill;
					ctx.fillText(this.label, -this.label.length*6, 25);
				}

				if(this.sided === 3){
					ctx.fillStyle = 'rgb(252,250,250)';
					ctx.fillRect(-6 - (5*this.label.length),-30,11+(10*this.label.length),23);
					ctx.font = "20px sans-serif";
					ctx.fillStyle = this.textfill;
					ctx.fillText(this.label, -this.label.length*6, -11);
				}
			}
		});

		function makeEdge(from,to,weight,canvas){
			if(from == to) return;
			to.vertex_from.push(from);
			var index = from.vertex_from.indexOf(to);
			if(index != -1) {
				from.line_to[index].set({sided: 3});
				var line = new Edge([from.left,from.top,to.left,to.top],{
					figure: "Edge",
					sided: true,
					vertex_to: to,
					vertex_from: from,
					label: weight, 
					stroke: '#9aadaa', 
					strokeWidth: 3.5, 
					selectable: false,
					originX: "center",
					originY: "center",
				});

				line.triangle = new fabric.Triangle({
					figure: "Edge",
					vertex_to: to,
					vertex_from: from,
					fill: '#9aadaa',
					left: line.x2,
					top: line.y2,
					angle: calcArrowAngle(line.x1, line.y1, line.x2, line.y2),
					originX: 0.5,
					originY: -0.35,
					hasBorders: false,
					hasControls: false,
					lockScalingX: true,
					lockScalingY: true,
					lockRotation: true,
					pointType: 'arrow_start',
					width: 25,
					height: 35,
					selectable: false,
				});
				from.line_from.push(line);
				to.line_to.push(line);

				canvas.add(line, line.triangle);
				canvas.sendToBack(line);
				//canvas.bringToFront(line.triangle);
			}
			else{
				var line = new Edge([from.left,from.top,to.left,to.top],{
					figure: "Edge",
					sided: false,
					vertex_to: to,
					vertex_from: from,
					label: weight, 
					stroke: '#9aadaa', 
					strokeWidth: 3.5, 
					selectable: false,
					originX: "center",
					originY: "center",
				});

				line.triangle = new fabric.Triangle({
					figure: "Edge",
					vertex_to: to,
					vertex_from: from,
					fill: '#9aadaa',
					left: line.x2,
					top: line.y2,
					angle: calcArrowAngle(line.x1, line.y1, line.x2, line.y2),
					originX: 0.5,
					originY: -0.35,
					hasBorders: false,
					hasControls: false,
					lockScalingX: true,
					lockScalingY: true,
					lockRotation: true,
					pointType: 'arrow_start',
					width: 25,
					height: 35,
					selectable: false,
				});

				from.line_from.push(line);
				to.line_to.push(line);

				canvas.add(line, line.triangle);
				canvas.sendToBack(line);
				canvas.bringToFront(line.triangle);
			}
			return line;
		}

		function calcArrowAngle(x1, y1, x2, y2) {
			var angle = 0,
				x, y;

			x = (x2 - x1);
			y = (y2 - y1);

			if (x === 0) angle = (y === 0) ? 0 : (y > 0) ? Math.PI / 2 : Math.PI * 3 / 2;
			else if (y === 0) angle = (x > 0) ? 0 : Math.PI;
			else angle = (x < 0) ? Math.atan(y / x) + Math.PI : (y < 0) ? Math.atan(y / x) + (2 * Math.PI) : Math.atan(y / x);

			return (angle * 180 / Math.PI + 90);
		}

		function setHeader(xhr) {
			//xhr.setRequestHeader("Authorization", 'Bearer mqvHg2_zkligBM0JreF5qgQ7II9x3fZG');
		}

		function setGraph(id){
			canvas.clear();
			for (key in graph) delete graph[key];
			$('#error').html("");
			$('#weight').html("");
			if(id == 1) $("#delete_graph").prop( "disabled", true);
			else $("#delete_graph").prop( "disabled", false );
			remainder_vertex = [];
			remainder_line = [];
			$.ajax({
				url: SERVER+'/graphs/'+id,
				data: {expand: 'coord'}, 
				datatype: 'json',
				type: 'GET', 
				headers: header,
				success: function(data){
					for (var i = 0; i < data.vertices.length; i++){
						var v = data.vertices[i];
						graph[v.alias] = new Vertex(v.alias,{left: v.X, top: v.Y});
						canvas.add(graph[v.alias]);
					}
					$.ajax({
						url: SERVER+'/rest/edge/'+id,
						type: 'GET',
						headers: header,
						error: function(e) { $('#error').html("ошибка сервера: "+e.responseJSON.name+", "+e.responseJSON.message);},
						success: function(e) { 
							for (var i = 0; i < e.length; i++){
								var from = graph[e[i].vertex_from];
								var to = graph[e[i].vertex_to];
								var weight = String(e[i].weight);
								makeEdge(from,to,weight,canvas);
							}
						},
						beforeSend: setHeader,
					});
				},
				error:function(e){
					$('#error').html("ошибка сервера: "+e.responseJSON.name+", "+e.responseJSON.message);
				},
				statusCode:{404 :function(){
					$.ajax({url: SERVER+'/graphs', datatype: 'json', type: 'POST', headers: header, data: {name: "main", id: 1}, success: function(e){$("#graph_list").append('<option selected id="1">main</option>');}, beforeSend: setHeader});
					$("#graph_list").find("#1").prop('selected', true);
					back_graph = [];
					id_graph = 1;
					setGraph(id_graph);
				}},
			});
		}
		setGraph(id_graph);

		$.ajax({
			url: SERVER+'/graphs', 
			datatype: 'json',
			type: 'GET', 
			headers: header,
			success: function(data){
				for (var i = 0; i < data.length; i++){
					if(data[i].id == id_graph) $("#graph_list").append('<option selected id="'+data[i].id+'">'+data[i].graph_name+'</option>');
					else $("#graph_list").append('<option id="'+data[i].id+'">'+data[i].graph_name+'</option>');
				}
			},
			error:function(e){
				$('#error').html("ошибка сервера: "+e.responseJSON.name+", "+e.responseJSON.message);
			},
		});

		$("#graph_list").change(function(){
			if($(this).val() == 0) return false;
			var val = $(this).val();
			var id = $(this).children(":selected").attr("id");
			$.cookie('graph', id);
			if(id == 1) back_graph = [];
			else back_graph.push(Number(id_graph));
			id_graph = id;
			setGraph(id);
		});

		$("#create_graph").click(function(){
			var name = prompt("Введите название графа", '');
			if(name != '' && name != null){
				$.ajax({
					url: SERVER+'/graphs',
					datatype: 'json',
					type: 'POST', 
					headers: header,
					data: {name: name},
					success: function(data){
						$("#graph_list").append('<option selected id="'+data.id+'">'+data.graph_name+'</option>');
						$.cookie('graph', data.id);
						back_graph.push(Number(id_graph));
						id_graph = data.id;
						setGraph(data.id);
					},
					statusCode:{422 :function(event){
						$('#error').html(event.responseJSON[0].message+" Пожалуйста, введите другое имя для граффа");
					}},
					error:function(event){
						$('#error').html("ошибка сервера");
					},
					beforeSend: setHeader,
				});
			}
		});

		$("#delete_graph").click(function(){
			if(id_graph == 1) return;
			if(back_graph.length == 0) back_graph = ['1'];
			var isDelete = confirm("Вы уверены, что хотите удалить этот граф?");
			if(isDelete){
				$.ajax({
					url: SERVER+'/graphs/'+id_graph,
					datatype: 'json',
					type: 'DELETE', 
					headers: header,
					success: function(data){
						$("#graph_list").find("#"+id_graph).remove();
						for (var i = 0; i < back_graph.length; i++) if(back_graph[i] == id_graph) delete back_graph[i];
						back_graph = back_graph.filter(function(x) {
							return x !== undefined && x !== null; 
						});
						var back_id = back_graph.pop();
						$("#graph_list").find("#"+back_id).prop('selected', true);
						$.cookie('graph', back_id);
						id_graph = back_id;
						setGraph(back_id);
					},
					error:function(event){
						$('#error').html("ошибка сервера");
					},
					beforeSend: setHeader,
				});
			}
		});

		$( "#relate" ).click(function(event) {
			var arr = $("#Edge").serializeArray();
			for (var i = 0; i < arr.length; i++){
				if(arr[i].name == "from") var from = arr[i].value;
				if(arr[i].name == "to") var to = arr[i].value;
			}
			var weight = prompt("Введите вес для ребра", '');
			if(weight != null){
				$.ajax({
					url: SERVER+'/rest/relate/'+id_graph,
					datatype: 'json',
					type: 'PUT', 
					headers: header,
					data: {vertex_from: from, vertex_to: to, weight: weight},
					success: function(data){
						setGraph(id_graph);
					},
					statusCode:{422 :function(event){
						$('#error').html(event.responseJSON[0].message+" Пожалуйста, введите другие данные");
					}},
					error:function(event){
							$('#error').html("ошибка сервера");
					},
					beforeSend: setHeader,
				});
			}
			return false;
		});

		$( "#dissociate" ).click(function(event) {
			var arr = $("#Edge").serializeArray();
			for (var i = 0; i < arr.length; i++){
				if(arr[i].name == "from") var from = arr[i].value;
				if(arr[i].name == "to") var to = arr[i].value;
			}
			var isDelete = confirm("Вы уверены, что хотите удалить связь между вершинами?");
			if(isDelete){
				$.ajax({
					url: SERVER+'/rest/dissociate/'+id_graph,
					datatype: 'json',
					type: 'PUT', 
					headers: header,
					data: {vertex_from: from, vertex_to: to},
					success: function(data){
						setGraph(id_graph);
					},
					statusCode:{
						422 :function(event){
							$('#error').html(event.responseJSON[0].message+" Пожалуйста, введите другие данные");
						},
						404 :function(event){
							$('#error').html("данной связи не существует");
						}
					},
					error:function(event){
						$('#error').html("ошибка сервера");
					},
					beforeSend: setHeader,
				});
			}
			return false;
		});

		$( "#shortest" ).click(function(event) {
			for(var i = 0; i < remainder_vertex.length; i++){
				remainder_vertex[i].set({fill: "rgba(126,194,188,0.9)"});
			}
			for(var i = 0; i < remainder_line.length; i++){
				remainder_line[i].set({stroke: "#9aadaa", textfill: '#333'});
				remainder_line[i].triangle.set({fill: "#9aadaa"});
				canvas.sendToBack(remainder_line[i]);
			}
			canvas.renderAll();
			remainder_vertex = [];
			remainder_line = [];
			var arr = $("#Edge").serializeArray();
			for (var i = 0; i < arr.length; i++){
				if(arr[i].name == "from") var from = arr[i].value;
				if(arr[i].name == "to") var to = arr[i].value;
			}
			$.ajax({
				url: SERVER+'/rest/shortest/'+id_graph,
				datatype: 'json',
				type: 'PUT', 
				headers: header,
				data: {from: from, to: to},
				//beforeSend: function(){ setGraph(id_graph); },
				success: function(data){
					$('#weight').html("вес кратчайшего пути: "+data.total);
					$('#error').html("");
					for(var i = 0; i < data.route.length; i++){
						var v = graph[data.route[i]];
						var v_next = graph[data.route[i+1]];
						v.item(0).set({fill: "red"});
						remainder_vertex.push(v.item(0));
						canvas.renderAll();
						if(v_next == null) return;
						for(var j = 0; j < v.line_from.length; j++){
							if(v.line_from[j].vertex_to == v_next){
								remainder_line.push(v.line_from[j]);
								v.line_from[j].set({stroke: "red", textfill: '#a11212'});
								v.line_from[j].triangle.set({fill: "red"});
								canvas.bringToFront(v.line_from[j]);
							}
						}
					}
					canvas.renderAll();
				},
				statusCode:{
					422 :function(event){
						$('#weight').html("");
						$('#error').html(event.responseJSON[0].message+" Пожалуйста, введите другие данные");
					},
					415 :function(event){
						$('#weight').html("");
						$('#error').html("такой путь нельзя проложить");
					},
					404 :function(event){
						$('#weight').html("");
						$('#error').html("данной связи не существует");
					}
				},
				error:function(event){
					$('#weight').html("");
					$('#error').html("ошибка сервера");
				},
				beforeSend: setHeader,
			});
			return false;
		});

		$("#help").mousemove(function(eventObject) {
			var text = "<div>1. Чтобы создать вершину, кликните двойным щелчком мыши на любое пустое поле графа.</div> <div>2. Чтобы удалить вершину, кликните двойным щелчком мыши по той вершине, которую вы хотите удалить.</div> <div>3. В поле ввода данных, «from» и «To» вы можете ввести две вершины, с определённой направленностью (от вершины «from» к вершине «To»), для того чтобы создать ребро, удалить ребро или найти кратчайший путь.</div> <div>4. Удалить граф «main» вы можете только в праздничные дни. </div>";
			$("#tooltip").html(text).css({"top" : eventObject.pageY + 5,"left" : eventObject.pageX - 250}).show();
		}).mouseout(function () {
			$("#tooltip").hide().html("").css({"top" : 0, "left" : 0});
		});

		canvas.on('mouse:dblclick', function(event) {
			var e = event.e;
			var t = event.target;
			if(t == null || t.figure == "Edge"){
				var name = prompt("Введите название вершины для графа", '');
				if(name != null){
					if(name.trim() == ""){
						$('#error').html("название вершины не может быть пустым"); 
						return;
					}
					if(name.length >5){
						$('#error').html("название вершины не может быть больше 5 символов");
						return;
					}
					if(!/^[a-z]\w*$/i.test(name)){
						$('#error').html("название вершины может содержать только латинские символы, числовые символы и знак подчеркивания");
						return;
					}
					$.ajax({
						url: SERVER+'/graphs/'+id_graph,
						datatype: 'json',
						type: 'PUT', 
						headers: header,
						data: {vertices: {alias: name, X: e.layerX, Y: e.layerY} },
						error:function(e){
							$('#error').html("ошибка сервера: "+e.responseJSON.name+", "+e.responseJSON.message);
						},
						success: function(data){
							setGraph(id_graph);
						},
						beforeSend: setHeader,
					});
				}
			}
			else if(t.figure == "Vertex"){
				var isDelete = confirm("Вы уверены, что хотите удалить эту вершину?");
				if(isDelete){
					$.ajax({
						url: SERVER+'/graphs/'+id_graph,
						datatype: 'json',
						type: 'PUT', 
						headers: header,
						data: {change: "delete", vertices: {alias: t.label } },
						error:function(e){
							$('#error').html("ошибка сервера: "+e.responseJSON.name+", "+e.responseJSON.message);
						},
						success: function(data){
							setGraph(id_graph);
						},
						beforeSend: setHeader,
					});
				}
			}
			/*else if(t.figure == "Edge"){
				var from = t.vertex_from.label;
				var to = t.vertex_to.label;
				var isDelete = confirm("Вы уверены, что хотите разорвать связь между вершинами от "+from+" к "+to+"?");
				if(isDelete){
					$.ajax({
						url: SERVER+'/web/dissociate/'+id_graph,
						datatype: 'json',
						type: 'PUT', 
						headers: header,
						data: {vertex_from: from, vertex_to: to},
						success: function(data){
							setGraph(id_graph);
						},
						statusCode:{422 :function(event){
							$('#error').html(event.responseJSON[0].message+" Пожалуйста, введите другие данные");
						}},
						statusCode:{404 :function(event){
							$('#error').html("данной связи не существует");
						}},
						error:function(event){
							$('#error').html("ошибка сервера");
						},
						beforeSend: setHeader,
					});
				}
			}*/
		});

		canvas.on('object:modified', function(event) {
			var e = event.e;
			var t = event.target;
			$.ajax({
				url: SERVER+'/graphs/'+id_graph,
				datatype: 'json',
				type: 'PUT', 
				headers: header,
				data: {vertices: {alias: t.label, X: t.left, Y: t.top} },
				error:function(e){
					$('#error').html("ошибка сервера: "+e.responseJSON.name+", "+e.responseJSON.message);
				},
				beforeSend: setHeader,
			});
		});

		canvas.on('object:moving', function(event) {
			var p = event.target;
			for (var i = 0; i < p.line_from.length; i++){
				p.line_from[i] && p.line_from[i].set({x1: p.left, y1: p.top});
				p.line_from[i].triangle && p.line_from[i].triangle.set({angle: calcArrowAngle(p.line_from[i].x1, p.line_from[i].y1, p.line_from[i].x2, p.line_from[i].y2)});
			}
			for (var i = 0; i < p.line_to.length; i++) {
				p.line_to[i] && p.line_to[i].set({x2: p.left, y2: p.top});
				p.line_to[i].triangle && p.line_to[i].triangle.set({left: p.left, top: p.top, angle: calcArrowAngle(p.line_to[i].x1, p.line_to[i].y1, p.line_to[i].x2, p.line_to[i].y2)});
			}
			canvas.renderAll();
	  	});
	})();
	</script>
</section>