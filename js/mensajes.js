var respuesta = null;
function mensajesBC(){
    
}

mensajesBC.prototype.noticia = function(tit, txt){
    msjBC.create_mjs(tit, txt,'#f39c12 '); 
};

mensajesBC.prototype.informacion = function(tit, txt){
    msjBC.create_mjs(tit, txt,'#3c8dbc ');
};


mensajesBC.prototype.ok = function(tit, txt) {
    msjBC.create_mjs(tit, txt,'#00a65a') ;  
};

mensajesBC.prototype.error = function(tit, txt) {
    msjBC.create_mjs(tit, txt,'#dd4b39');
   
};
/*
 * 
 */
mensajesBC.prototype.create_mjs = function(tit, txt, col) {
    var x = document.getElementById("snackbar");
    if(x){
        x.innerHTML = '<strong>'+tit+'</strong><br>'+txt;
        x.className = "show";
        x.style.backgroundColor = col;
        setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);    
    }else{
        throw new Error('Cree un div con id snackbar');
    }
        
};


var msjBC = new mensajesBC();


