<div id ="task2" style="border: 1px solid black; width: 300px; height: 300px;"></div>

<?php $this->registerJs('$("#task2").trackCoords({url: "/save.php", checkInterval: 30});'); ?>

<script>
	(function( $ ){
	$.fn.trackCoords = function(options){
		if(!options.url) return false;
		$.ajax({url: options.url, type:'HEAD', error: function(){
			return false;
		}});

		var settings = $.extend({checkInterval: 30, sendInterval: 3}, options);
		var coords = {x: null, y: null};
		var send = [];
		var check = false;
		var i = 0;
		var time = 0;
		//var time2 = new Date().getTime();

		// обновляет координаты в объекте coords
		this.on("mousemove",function(e){
			var pos = $(this).offset();
			coords.x = (e.pageX - pos.left);
			coords.y = (e.pageY - pos.top);
		});

		//собирает данные
		this.on("mouseover",function(e){
			check = true;
		});

		//не собирает данные
		this.on("mouseout",function(e){
			check = false;
		});

		//устанавливаем период сбора данных (координаты и времени)
		window.setInterval(function(){
			if(check){
				if(send[i] && (send[i].X != coords.x || send[i].Y != coords.y)) i++;
				time = time + settings.checkInterval;
				send[i] = {X: coords.x, Y: coords.y, time: time};
				console.log("=============")
				console.log(i);
				console.log(send[i]);
			}
		}, settings.checkInterval);

		//отправляем данные серверу и обнуляем массив
		window.setInterval(function(){
			if(send.length != 0){
				$.post(settings.url,{send:send});
				send = [];
				i = 0;
			}
		}, settings.sendInterval * 1000);
	};
})(jQuery);
</script>