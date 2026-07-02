/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class update_activities_uvd{
    
    /*
     * Quitar formulario propio de la actividad y poner el de la actividad propuesta
     * @param {obj} ob
     * @param {string} act
     * @param {string} id_tag
     * @param {obj} info_act
     * @param {string} url
     * @returns {Generator}
     */
    redirec_template(ob, act, id_tag, info_act, url, type, rubric){
        document.getElementById('overlay-loader_block_modedit').style.display = 'block';
        var consulta = JSON.parse(ob);
        var info_act = JSON.parse(info_act);
        this.type = type;
        this.url = url;
        this.consulta = consulta;
        this.info_act = info_act;
        this.act = act;
        this.id_tag = id_tag+this.consulta.plantillaid;
        var pag = document.getElementById('mform1');
        if(pag){
            pag.innerHTML = consulta.actividad;
            var pag = document.getElementById('mform1');
            this.all_pag = pag;
            var titleActivity = document.getElementById('titleActivity');
            var pag_tag = document.getElementById(this.id_tag);
            pag.innerHTML = '';
            if(this.act != 'urls'){
                pag.appendChild(titleActivity);
            }
            pag.appendChild(pag_tag);
            if(this.act == 'urls'){
                var urls = JSON.parse(consulta.obj).urls;
                var tags_url = document.getElementsByClassName('separator');
                for(var k in tags_url){
                    if(tags_url[k]  && tags_url[k].style){
                        if(urls[k].id != this.info_act.id){
                            tags_url[k].style.display = 'none';
                        }
                    }
                }
                
            }
            pag.innerHTML += '<button type="button" class="btn btn-primary" id="btn_proponerActi" onclick="u_act_uvd.objets_update()">'+
                                '<i class="fa fa-save"></i>'+
                                'Enviar actualización'+
                            '</button>'+
                            '<button type="button" class="btn btn-success" id="btn_saveActi" onclick="location.href = \' '+url+'/course/view.php?id='+consulta.courseid_h+' \'">'+
                                '<i class="fa fa-share-square"></i>'+
                                'Volver al curso'+
                            '</button>';
        }else{
            console.log('no se ha creado mform1');
        }
        document.getElementById('overlay-loader_block_modedit').style.display = 'none';
    }
    
    
    objets_update(){
        document.getElementById('overlay-loader_block_modedit').style.display = 'block';
        var html_act = this.consulta.actividad;
        if(this.act == 'urls'){
            var urls = JSON.parse(this.consulta.obj).urls;
            var tags_url = document.getElementsByClassName('separator');
            for(var k in urls){
                if(tags_url[k] && tags_url[k].style){
                    tags_url[k].style.display = 'block';
                    if(urls[k].id == this.info_act.id){
                        this.position = k;
                        this.tag_url = tags_url[k];
                    }
                }
            }
        }
        var new_html = document.getElementById(this.id_tag).innerHTML;
        var name = (this.act == 'urls') ? this.tag_url.getElementsByClassName('offline')[0].innerHTML:document.getElementById('titleActivity').getElementsByTagName('div')[0].innerHTML;

        this.all_pag.innerHTML = html_act;
        document.getElementById(this.id_tag).innerHTML = new_html; //dar el nuevo valor a la modificacion html
        if(this.act != 'urls'){
            document.getElementById('titleActivity').getElementsByTagName('div')[0].innerHTML = name;
        }
        this.consulta.actividad = this.all_pag.innerHTML;
        this.info_act.name = name;
        if(this.act != 'urls'){
            this.info_act.intro = new_html;
        }else{
            this.info_act.externalurl = this.tag_url.getElementsByClassName('message')[0].innerHTML;
        }
        //actualizar el objeto en la tabla act_obj_create, columna obj
        var new_obj = JSON.parse(this.consulta.obj);
        if(this.act != 'urls'){
            new_obj[this.act] = this.info_act;
        }else{
            new_obj[this.act][this.position] = this.info_act;
        }
        
        this.consulta.obj = JSON.stringify(new_obj);
        this.info_act.instance = this.info_act.id;
        var info = {
            type: this.type,
            objeto: JSON.stringify(this.consulta),
            actividad: JSON.stringify(this.info_act),
            key: 'U02'
        };
        u_act_uvd.save_update(info);
    }
    
    
    /*
     * 
     * @param {obj} info
     * @returns {Generator}
     */
    save_update(info){
        var cursoid = this.consulta.courseid_h;
        var url = this.url;
        $.ajax({
            url : '../methods/UPD/class.update.php',
            data : info,
            type : 'POST',
            success : function(json) {
                
            },
            error : function(result, textStatus, errorThrown) {
                document.getElementById('mform1').innerHTML = result.status;
            },
            complete : function(json, status) {
                if(status != 'error'){   
      
                    document.getElementById('mform1').innerHTML = '<link href="../../css/style.css" rel="stylesheet" type="text/css" />'+
                                                            '<script src="../../update/js/buttons.js"></script>'+
                                                            '<script src="../../update/js/objetsUpdates/updateObjet.js"></script>'+
                                                            '<script src="../../update/js/objetsUpdates/saveLog.js"></script>   '+
                                                            '<script src="../../update/js/objetsUpdates/CRE.js"></script>'+
                                                            '<script src="../../js/objetos/QRY.js"></script>'+
                                                            '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>'+
                                                            '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
                    var objUp = JSON.parse(json.responseText);
                    SLog.confir_nodos_actu(objUp[0], objUp[1], objUp[2], objUp[3], info.actividad,objUp[4], "../../", objUp[5]);

                }else{
                    document.getElementById('mform1').innerHTML = json.responseText;
                }
            }
        });
    }
}
var u_act_uvd = new update_activities_uvd();

