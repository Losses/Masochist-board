var i = new Image();
i.onerror = (function(){
    location.replace('offline.html');
});

i.src = 'images/online.png?d=' + escape(Date());