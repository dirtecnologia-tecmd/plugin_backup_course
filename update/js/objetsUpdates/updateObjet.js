/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function objetosActivitiesUpdate(typeAct) {}
ObAc = new objetosActivitiesUpdate();


objetosActivitiesUpdate.prototype.switchActivitiesUpdate = function (typeAct, id_course, id_act) {
    var obActi = null;

    switch (typeAct) {
        case 'course':
            obActi = ObAc.curso(id_course);
            break;
        case 'assign':
            obActi = ObAc.assign(id_act);
            break;
        case 'attendance':
            obActi = ObAc.attendance(id_act);
            break;
        case 'book':
            obActi = ObAc.book(id_act);
            break;
        case 'certificate':
            obActi = ObAc.certificate(id_act);
            break;
        case 'chat':
            obActi = ObAc.chat(id_act);
            break;
        case 'choice':
            obActi = ObAc.choice(id_act);
            break;
        case 'choicegroup':
            obActi = ObAc.choicegroup(id_act);
            break;
        case 'collaborate':
            obActi = ObAc.collaborate(id_act);
            break;
        case 'customcert':
            obActi = ObAc.customcert(id_act);
            break;
        case 'data':
            obActi = ObAc.data(id_act);
            break;
        case 'feedback':
            obActi = ObAc.feedback(id_act);
            break;
        case 'folder':
            obActi = ObAc.folder(id_act);
            break;
        case 'forum':
            obActi = ObAc.forum(id_act);
            break;
        case 'game':
            obActi = ObAc.game(id_act);
            break;
        case 'glossary':
            obActi = ObAc.glossary(id_act);
            break;
        case 'groupselect':
            obActi = ObAc.groupselect(id_act);
            break;
        case 'hvp':
            obActi = ObAc.hvp(id_act);
            break;
        case 'h5pactivity':
            obActi = ObAc.h5pactivity(id_act);
            break;
        case 'imscp':
            obActi = ObAc.imscp(id_act);
            break;
        case 'label':
            obActi = ObAc.label(id_act);
            break;
        case 'lesson':
            obActi = ObAc.lesson(id_act);
            break;
        case 'lti':
            obActi = ObAc.lti(id_act);
            break;
        case 'page':
            obActi = ObAc.page(id_act);
            break;
        case 'pearson':
            obActi = ObAc.pearson(id_act);
            break;
        case 'quiz':
            obActi = ObAc.quiz(id_act);
            break;
        case 'resource':
            obActi = ObAc.resource(id_act);
            break;
        case 'scorm':
            obActi = ObAc.scorm(id_act);
            break;
        case 'survey':
            obActi = ObAc.survey(id_act);
            break;
        case 'url':
            obActi = ObAc.url(id_act);
            break;
        case 'wiki':
            obActi = ObAc.wiki(id_act);
            break;
        case 'workshop':
            obActi = ObAc.workshop(id_act);
            break;
        default:
    }
    return obActi;
};


/* Objeto para crear en las tablas updates_courses y updates_log
 * @param {obj} data
 * @returns {objetosCrearUpdate.prototype.obC01.CRE_C01|Object}
 */
objetosActivitiesUpdate.prototype.assign = function (id_act) {
    /*
     * 
     * @type Object
     * Objeto para crear assign
     * Todos los datos son parametros 
     * Envío de datos para creación
     */

    var assign = { // name en el form // name en la tabla
        assign: {
            //general: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            alwaysshowdescription: 'alwaysshowdescription',
            //tiposdeentrega: {
            assignsubmission_onlinetext_enabled: 'nosubmissions', //en cero busca en la tabla assign_plugin_config: onlinetext
            assignsubmission_file_enabled: 'nosubmissions', //en cero busca en la tabla assign_plugin_config: file
            //configuraciondeEntrega:{
            submissiondrafts: 'submissiondrafts', //Requiera aceptación del usuario pulsando sobre el botón
            requiresubmissionstatement: 'requiresubmissionstatement',
            attemptreopenmethod: 'attemptreopenmethod',
            maxattempts: 'maxattempts',
            //configuraciónDeEntregaPorGrupo:{
            teamsubmission: 'teamsubmission',
            preventsubmissionnotingroup: 'preventsubmissionnotingroup',
            requireallteammemberssubmit: 'requireallteammemberssubmit',
            teamsubmissiongroupingid: 'teamsubmissiongroupingid',
            //avisos: {
            sendnotifications: 'sendnotifications',
            sendlatenotifications: 'sendlatenotifications',
            sendstudentnotifications: 'sendstudentnotifications',
            //calificaciones
            grade: 'grade',

            blindmarking: 'blindmarking', //Ocultar identidad
            markingworkflow: 'markingworkflow',
            markingallocation: 'markingallocation',
            areaid: 'areaid',
            rubric: 'rubric',
            options: 'options',
            status: 'status',
            descriptiontrust: 'descriptiontrust',
            description: 'description',
            descriptionformat: 'descriptionformat'
        },
        assign_plugin_config: {
            instance: 'assignment',
            //plugin: onlinetext:{ assignsubmission_onlinetext_enabled
            assignsubmission_onlinetext_wordlimit: 'value', //name: wordlimit in column name   tiposdeentrega
            assignsubmission_onlinetext_wordlimit_enabled: 'value', //name: wordlimitenabled in column name   tiposdeentrega
            //plugin: file:{ assignsubmission_file_enabled
            assignsubmission_file_maxfiles: 'value', //name: maxfilesubmissions   tiposdeentrega
            assignsubmission_file_maxsizebytes: 'value', //name: maxsubmissionsizebytes    tiposdeentrega
            assignsubmission_file_filetypes: 'value', //name: filetypeslist   tiposdeentrega

            assignfeedback_file_enabled: 'value', // name:enabled   tiposdeRetroalimentación
            //plugin: comments: {
            assignfeedback_comments_enabled: 'value', //name: enabled  tiposdeRetroalimentación
            assignfeedback_comments_commentinline: 'value', //name: commentinline  tiposdeRetroalimentación
            //plugin: offline: {
            assignfeedback_offline_enabled: 'value' // name: enabled commentinline  tiposdeRetroalimentación
        },
        grading_areas: {
            advancedgradingmethod_submissions: 'activemethod' //Método de calificación
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradetype: 'gradetype',
            grade: 'grademax',
            /*'grade[modgrade_type]': 'gradetype', //ninguna: 3, escale: 2, puntuacion: 1
            'grade[modgrade_scale]': 'scaleid', //cunatitativa completa: 3, defaut:2,  separate:1
            'grade[modgrade_point]': 'grademax', //puntuación: valor*/
            gradepass: 'gradepass',
            gradecat: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };

    return assign;
};

objetosActivitiesUpdate.prototype.attendance = function () {
    var attendance = {
        attendance: {
            //general: { 
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            //calificaciones
            grade: 'grade',
            /*'grade[modgrade_type]': 'grade', //ninguna: 0, 
            'grade[modgrade_scale]': 'grade', //cunatitativa completa: -3, defaut:-2,  separate: -1
            'grade[modgrade_point]': 'grade', //puntuación: valor*/
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradetype: 'gradetype',
            grade: 'grademax',
            gradepass: 'gradepass',
            gradecat: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            cmidnumber: 'idnumber',
            showdescription: 'showdescription',
            availabilityconditionsjson: 'availability'
        }
    };
    return attendance;
};

objetosActivitiesUpdate.prototype.book = function () {
    var book = {
        book: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            //apariencia
            numbering: 'numbering',
            navstyle: 'navstyle',
            customtitles: 'customtitles'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            availabilityconditionsjson: 'availability'
        }
    };
    return book;
};

objetosActivitiesUpdate.prototype.certificate = function () {
    var certificate = {
        certificate: {
            /* no debe traer nada de esto */
        }
    };
    return certificate;
};

objetosActivitiesUpdate.prototype.chat = function () {
    var chat = {
        chat: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return chat;
};

objetosActivitiesUpdate.prototype.choice = function () {
    var choice = {
        choice: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            allowupdate: 'allowupdate',
            allowmultiple: 'allowmultiple',
            limitanswers: 'limitanswers',
            showresults: 'showresults',
            publish: 'publish',
            showunanswered: 'showunanswered',
            includeinactive: 'includeinactive'
        },
        choice_options: {
            instance: 'chiceid',
            'optionid[0]': 'id',
            'option[0]': 'text',
            'limit[0]': 'maxanswers',
            'optionid[1]': 'id',
            'option[1]': 'text',
            'limit[1]': 'maxanswers',
            'optionid[2]': 'id',
            'option[2]': 'text',
            'limit[2]': 'maxanswers',
            'optionid[3]': 'id',
            'option[3]': 'text',
            'limit[3]': 'maxanswers',
            'optionid[4]': 'id',
            'option[4]': 'text',
            'limit[4]': 'maxanswers',
            'optionid[5]': 'id',
            'option[5]': 'text',
            'limit[5]': 'maxanswers',
            'optionid[6]': 'id',
            'option[6]': 'text',
            'limit[6]': 'maxanswers',
            'optionid[7]': 'id',
            'option[7]': 'text',
            'limit[7]': 'maxanswers',
            'optionid[8]': 'id',
            'option[8]': 'text',
            'limit[8]': 'maxanswers',
            'optionid[9]': 'id',
            'option[9]': 'text',
            'limit[9]': 'maxanswers',
            'optionid[10]': 'id',
            'option[10]': 'text',
            'limit[10]': 'maxanswers'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return choice;
};

objetosActivitiesUpdate.prototype.choicegroup = function () {
    var choicegroup = {
        choicegroup: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            multipleenrollmentspossible: 'multipleenrollmentspossible',
            showresults: 'showresults',
            publish: 'publish',
            allowupdate: 'allowupdate',
            showunanswered: 'showunanswered',
            limitanswers: 'limitanswers',
            sortgroupsby: 'sortgroupsby'
        },
        choicegroup_options: {
            instance: 'choicegroupid',
            generallimitation: 'maxanswers',
            serializedselectedgroups: 'groupid', //quita o inserta elementos en la tabla // en el formulario están unidos por ,
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            cmidnumber: 'idnumber',
            showdescription: 'showdescription',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return choicegroup;
};

objetosActivitiesUpdate.prototype.collaborate = function () {
    var collaborate = {
        collaborate: {}
    };
    return collaborate;
};

objetosActivitiesUpdate.prototype.customcert = function () {
    var customcert = {
        customcert: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat'
        }
    };
    return customcert;
};
objetosActivitiesUpdate.prototype.data = function () { //scale
    var data = {
        data: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            approval: 'approval',
            manageapproved: 'manageapproved',
            comments: 'comments',
            requiredentries: 'requiredentries',
            requiredentriestoview: 'requiredentriestoview',
            maxentries: 'maxentries',
            assessed: 'assessed'
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradetype: 'gradetype',
            scale: 'grademax',
            gradepass: 'gradepass',
            gradecat: 'categoryid',
            categoryid: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return data;
};

objetosActivitiesUpdate.prototype.feedback = function () {
    var feedback = {
        feedback: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            anonymous: 'anonymous',
            multiple_submit: 'multiple_submit',
            email_notification: 'email_notification',
            autonumbering: 'autonumbering',
            publish_stats: 'publish_stats',
            page_after_submit: 'page_after_submit',
            page_after_submitformat: 'page_after_submitformat',
            site_after_submit: 'site_after_submit'
        },

        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            showdescription: 'showdescription',
            availabilityconditionsjson: 'availability'
        },
        feedback_item: {
            id: 'id',
            feedback: 'feedback',
            template: 'template',
            name: 'name',
            label: 'label',
            presentation: 'presentation',
            typ: 'typ',
            hasvalue: 'hasvalue',
            position: 'position',
            required: 'required',
            dependitem: 'dependitem',
            dependvalue: 'dependvalue',
            options: 'options',
        }

    };
    ///mod/feedback/edit.php?id=934 añadir
    // /mod/feedback/edit_item.php editar
    if (window.location.href.indexOf('layouts/feedback/edit_item') != -1 || window.location.href.indexOf('layouts/feedback/edit.php') != -1) {
        feedback.feedback_item = {
                id: 'id',
                feedback: 'feedback',
                template: 'template',
                name: 'name',
                label: 'label',
                presentation: 'presentation',
                typ: 'typ',
                hasvalue: 'hasvalue',
                position: 'position',
                required: 'required',
                dependitem: 'dependitem',
                dependvalue: 'dependvalue',
                options: 'options',
            },
            feedback.feedback = {
                feedback: 'id',
            },
            feedback.course_modules = {}
    }

    return feedback;
};

objetosActivitiesUpdate.prototype.folder = function () {

    var folder = {

        folder: {
            id: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            display: 'display',
            showexpanded: 'showexpanded',
            showdownloadfolder: 'showdownloadfolder',
            forcedownload: 'forcedownload',
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            cmidnumber: 'idnumber',
            visible: 'visible',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            completion: 'completion',
            completiongradeitemnumber: 'completiongradeitemnumber',
            completionview: 'completionview',
            completionexpected: 'completionexpected',
            completionpassgrade: 'completionpassgrade',
            showdescription: 'showdescription',
            availabilityconditionsjson: 'availability'
        }

    };

    return folder;

}

objetosActivitiesUpdate.prototype.forum = function () {
    var forum = {

        forum: {
            instance: 'id',
            areaid: 'areaid',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            type: 'type',
            maxbytes: 'maxbytes',
            maxattachments: 'maxattachments',
            displaywordcount: 'displaywordcount',
            description_editor: 'description_editor',
            rubric: 'rubric',
            options: 'options',
            status: 'status',
            descriptiontrust: 'descriptiontrust',
            description: 'description',
            descriptionformat: 'descriptionformat'
        },

        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        },

    };

    return forum;
};

objetosActivitiesUpdate.prototype.game = function () {
    var game = {
        game: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            sourcemodule: 'sourcemodule',
            disablesummarize: 'disablesummarize',
            gamekind: 'gamekind',
            quizid: 'quizid',
            glossaryid: 'glossaryid',
            glossarycategoryid: 'glossarycategoryid',
            questioncategoryid: 'questioncategoryid',
            bookid: 'bookid',
            param1: 'param1', //Mostrar la primera letra de ahorcado
            param2: 'param2', //Mostrar la última letra del ahorcado
            param3: 'param3',
            param4: 'param4', //Número de palabras por juego
            param5: 'param5', // ¿Mostrar las preguntas?
            param6: 'param6', //Mostrar la respuesta correcta después del final
            param7: 'param7', //Permitir espacios en las palabras
            param8: 'param8', //Permitir el símbolo - en las palabras
            param9: 'param9',
            param10: 'param10', //Máximo número de errores (deben ser imágenes llamadas hangman_0.jpg, hangman_1.jpg, ...)

            shuffle: 'shuffle',
            toptext: 'toptext',
            bottomtext: 'bottomtext',
            //calificaciones
            grade: 'grade', //ninguna: 0,  //puntuación: valor
            grademethod: 'grademethod',
            decimalpoints: 'decimalpoints',
            popup: 'popup',
            review: 'review',
            attempts: 'attempts',
            glossaryid2: 'glossaryid2',
            glossarycategoryid2: 'glossarycategoryid2',

            language: 'language', //Idioma de las palabras
            userlanguage: 'userlanguage', //User defined language
            maxattempts: 'maxattempts',
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradepass: 'gradepass',
            gradecat: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return game;
};

objetosActivitiesUpdate.prototype.glossary = function () {
    var glossary = {
        glossary: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            mainglossary: 'mainglossary',
            defaultapproval: 'defaultapproval',
            editalways: 'editalways',
            allowduplicatedentries: 'allowduplicatedentries',
            allowcomments: 'allowcomments',
            usedynalink: 'usedynalink',
            displayformat: 'displayformat',
            approvaldisplayformat: 'approvaldisplayformat',
            entbypage: 'entbypage',
            showalphabet: 'showalphabet',
            showall: 'showall',
            showspecial: 'showspecial',
            allowprintview: 'allowprintview'
        },

        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            cmidnumber: 'idnumber',
            showdescription: 'showdescription',
            availabilityconditionsjson: 'availability'
        }
    };
    return glossary;
};

objetosActivitiesUpdate.prototype.groupselect = function () {
    var groupselect = {
        groupselect: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            targetgrouping: 'targetgrouping',
            maxmembers: 'maxmembers',
            timeavailable: 'timeavailable',
            timedue: 'timedue',
            hidefullgroups: 'hidefullgroups',
            hidesuspendedstudents: 'hidesuspendedstudents',
            hidegroupmembers: 'hidegroupmembers',
            deleteemptygroups: 'deleteemptygroups',
            studentcancreate: 'studentcancreate',
            minmembers: 'minmembers',
            assignteachers: 'assignteachers',
            studentcansetdesc: 'studentcansetdesc',
            showassignedteacher: 'showassignedteacher',
            studentcansetenrolmentkey: 'studentcansetenrolmentkey',
            studentcansetgroupname: 'studentcansetgroupname',
            notifyexpiredselection: 'notifyexpiredselection',
            supervisionrole: 'supervisionrole',
            maxgroupmembership: 'maxgroupmembership',
            studentcanjoin: 'studentcanjoin',
            studentcanleave: 'studentcanleave',
        },

        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            cmidnumber: 'idnumber',
            showdescription: 'showdescription',
            availabilityconditionsjson: 'availability'
        }
    };
    return groupselect;
};

objetosActivitiesUpdate.prototype.hvp = function () {
    var hvp = {
        hvp: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            mainglossary: 'mainglossary',
            defaultapproval: 'defaultapproval',
            editalways: 'editalways',
            allowduplicatedentries: 'allowduplicatedentries',
            allowcomments: 'allowcomments',
            usedynalink: 'usedynalink',
            displayformat: 'displayformat',
            approvaldisplayformat: 'approvaldisplayformat',
            entbypage: 'entbypage',
            showalphabet: 'showalphabet',
            showall: 'showall',
            showspecial: 'showspecial',
            allowprintview: 'allowprintview'
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradepass: 'gradepass',
            gradecat: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return hvp;
};

objetosActivitiesUpdate.prototype.h5pactivity = function () {
    var h5pactivity = {
        h5pactivity: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            mainglossary: 'mainglossary',
            defaultapproval: 'defaultapproval',
            editalways: 'editalways',
            allowduplicatedentries: 'allowduplicatedentries',
            allowcomments: 'allowcomments',
            usedynalink: 'usedynalink',
            displayformat: 'displayformat',
            approvaldisplayformat: 'approvaldisplayformat',
            entbypage: 'entbypage',
            showalphabet: 'showalphabet',
            showall: 'showall',
            showspecial: 'showspecial',
            allowprintview: 'allowprintview'
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradepass: 'gradepass',
            gradecat: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return h5pactivity;
};

objetosActivitiesUpdate.prototype.imscp = function () {
    var imscp = {
        imscp: {}
    };
    return imscp;
};

objetosActivitiesUpdate.prototype.label = function () {
    var label = {
        label: {
            instance: 'id',
            intro: 'intro',
            introformat: 'introformat',
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return label;
};

objetosActivitiesUpdate.prototype.lesson = function () {
    var lesson = {
        lesson: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            progressbar: 'progressbar',
            displayleft: 'displayleft',
            ongoing: 'ongoing',
            displayleftif: 'displayleftif',
            slideshow: 'slideshow',
            maxanswers: 'maxanswers',
            feedback: 'feedback',
            activitylink: 'activitylink',
            modattempts: 'modattempts',
            review: 'review',
            maxattempts: 'maxattempts',
            nextpagedefault: 'nextpagedefault',
            maxpages: 'maxpages',
            //calificaciones
            grade: 'grade',
            practice: 'practice',
            custom: 'custom',
            retake: 'retake',
            usemaxgrade: 'usemaxgrade',
            minquestions: 'minquestions'
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradetype: 'gradetype',
            grade: 'grademax',
            /*'grade[modgrade_type]': 'gradetype', //ninguna: 3, escale: 2, puntuacion: 1
            'grade[modgrade_scale]': 'scaleid', //cunatitativa completa: 3, defaut:2,  separate:1
            'grade[modgrade_point]': 'grademax', //puntuación: valor*/
            gradepass: 'gradepass',
            gradecat: 'categoryid'
        },
        course_modules: {
            coursemodule: 'id',
            id: 'id', ///mod/lesson/editpage.php?id=938&pageid=97
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        },
    };
    ///mod/lesson/editpage.php?id=938&pageid=97
    // el añadir un Cluster o Página de contenido  desde /mod/lesson/edit.php?id=938&mode=full mueve todos los prevpageid y nextpageid
    if (window.location.href.indexOf('/layouts/lesson/editpage.php') != -1 || window.location.href.indexOf('layouts/lesson/lesson.php') != -1) {
        lesson.lesson_pages = {
            id: 'lessonid',
            firstpage: 'prevpageid',
            qtype: 'qtype',
            title: 'title',

            display: 'display',
            layout: 'layout',
            qoption: 'qoption'
        }
        lesson.lesson_answers = {
                id: 'lessonid',

            },
            lesson.lesson = {
                id: 'id',
            },
            lesson.grade_items = {},
            lesson.course_modules = {}
    }


    return lesson;
};

objetosActivitiesUpdate.prototype.lti = function () {
    var lti = {
        lti: {
            instance: 'id',
            name: 'name',
            typeid: 'typeid',
            toolurl: 'toolurl',
            //calificaciones
            grade: 'grade',
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradetype: 'gradetype',
            grade: 'grademax',
            grademax: 'grademax',
            grademin: 'grademin',
            gradepass: 'gradepass',
            gradecat: 'categoryid',
            scaleid: 'scaleid'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            visible: 'visible',
            completion: 'completion',
            completiongradeitemnumber: 'completiongradeitemnumber',
            completionview: 'completionview',
            completionexpected: 'completionexpected',
            completionpassgrade: 'completionpassgrade',
            cmidnumber: 'idnumber',
            showdescription: 'showdescription',
            availabilityconditionsjson: 'availability',
        }
    };
    return lti;
};

objetosActivitiesUpdate.prototype.page = function () {
    var page = {
        page: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            content: 'content',
            contentformat: 'contentformat',
            displayoptions: 'displayoptions' // es un objeto, printheading,printintro
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            availabilityconditionsjson: 'availability'
        }
    };
    return page;
};

objetosActivitiesUpdate.prototype.pearson = function () {
    var pearson = {
        pearson: {}
    };
    return pearson;
};




objetosActivitiesUpdate.prototype.quiz = function () {
    var quiz = {
        quiz: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            timelimit: 'timelimit', // multiplicar timelimit[timeunit] * timelimit[number]
            //"timelimit[enabled]": 'timelimit', // si es 0
            //calificacion
            attempts: 'attempts',
            grademethod: 'grademethod',
            //Esquema repaginatenow
            questionsperpage: 'questionsperpage',
            navmethod: 'navmethod',

            shuffleanswers: 'shuffleanswers',
            preferredbehaviour: 'preferredbehaviour',
            canredoquestions: 'canredoquestions',
            attemptonlast: 'attemptonlast',


            //compotatmiento de preguntas

            reviewattempt: 'reviewattempt',
            /*attemptduring: '', //65536
            attemptimmediately: '', //4096
            attemptopen: '', //256
            attemptclosed: '', //16*/

            reviewcorrectness: 'reviewcorrectness',

            /*correctnessduring: '', //65536
            correctnessimmediately: '', //4096
            correctnessopen: '', //256
            correctnessclosed: '', //16*/

            reviewmarks: 'reviewmarks',
            /*marksduring: '', //65536
            marksimmediately: '', //4096
            marksopen: '', //256
            marksclosed: '', //16*/

            reviewspecificfeedback: 'reviewspecificfeedback',
            /*specificfeedbackduring: '',//65536
            specificfeedbackimmediately: '', //4096
            specificfeedbackopen: '', //256
            specificfeedbackclosed: '', //16*/

            reviewgeneralfeedback: 'reviewgeneralfeedback',
            /*generalfeedbackduring: '', //65536
            generalfeedbackimmediately: '', //4096
            generalfeedbackopen: '', //256
            generalfeedbackclosed: '', //16*/

            reviewrightanswer: 'reviewrightanswer',
            /*rightanswerduring: '', //65536
            rightanswerimmediately: '', //4096
            rightansweropen: '', //256
            rightanswerclosed: '', //16*/

            reviewoverallfeedback: 'reviewoverallfeedback',
            /*overallfeedbackduring: '', //65536
            overallfeedbackimmediately: '', //4096
            overallfeedbackopen: '', //256
            overallfeedbackclosed: '' //16*/
            //primero 65536 segundo 4096 tercero 256 ultimo 16 1y2: 69632  1,2y3: 6988 todos: 69904 1y3:4352
        },
        quiz_feedback: { //todas las Retroalimentación global si son modificadas se eliminan y vuelven a crear en el padre
            quizid: 'instance',
            questionsperpage: 'page',
            'feedbacktext[0][text]': 'feedbacktext',
            'feedbacktext[0][format]': 'feedbacktextformat',
            "feedbackboundaries[0]": 'mingrade',
            'feedbacktext[1][text]': 'feedbacktext',
            'feedbacktext[1][format]': 'feedbacktextformat',
            "feedbackboundaries[1]": 'mingrade',
            'feedbacktext[2][text]': 'feedbacktext',
            'feedbacktext[2][format]': 'feedbacktextformat',
            "feedbackboundaries[2]": 'mingrade'
        },
        quiz_slots: {
            quizid: 'instance',
            questionsperpage: 'page' // hace una repaginacion en page, de acuerdo a los page
        },
        grade_items: {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradepass: 'gradepass',
            gradecat: 'categoryid',
            submissiongradepass: 'gradepass',
            gradecategory: 'categoryid',
            grade: 'grademax',
            gradinggradecategory: 'categoryid',
            gradinggrade: 'grademax',
            gradinggradepass: 'gradepass',
        },
        grade_categories: {
            id: 'id', // si es vacio es un insert
            fullname: 'fullname',
            aggregation: 'aggregation',
            aggregateonlygraded: 'aggregateonlygraded',
            droplow: 'droplow',

        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }

    };
    if (window.location.href.indexOf('local/backup_course/update/layouts/question/question.php') != -1) {
        quiz.quiz = {
                cmid: 'cmid',
            },
            delete quiz.quiz_feedback;
        delete quiz.quiz_slots;
        delete quiz.grade_items;
        delete quiz.grade_categories;
        delete quiz.course_modules;
        quiz.question = {
            id: 'id',
            name: 'name',
            category: 'category',
            qtype: 'qtype',
            defaultmark: 'defaultmark',
            penalty: 'penalty'
        };
    }
    return quiz;
};

objetosActivitiesUpdate.prototype.resource = function () {
    var resource = {
        resource: {}
    };
    return resource;
};

objetosActivitiesUpdate.prototype.scorm = function () {
    var scorm = {
        scorm: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            updatefreq: 'updatefreq',
            packagefile: '', //OJO! es el paquete
            popup: 'popup',
            width: 'width',
            height: 'height',
            scrollbars: 'options', // es un objeto, scrollbars,directories,location,menubar,toolbar,status
            displayactivityname: 'displayactivityname',
            skipview: 'skipview',
            hidebrowse: 'hidebrowse',
            displaycoursestructure: 'displaycoursestructure',
            hidetoc: 'hidetoc',
            nav: 'nav',
            navpositionleft: 'navpositionleft',
            navpositiontop: 'navpositiontop',
            displayattemptstatus: 'displayattemptstatus',
            grademethod: 'grademethod',
            maxgrade: 'maxgrade',
            maxattempt: 'maxattempt',
            whatgrade: 'whatgrade',
            forcenewattempt: 'forcenewattempt',
            lastattemptlock: 'lastattemptlock'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return scorm;
};

objetosActivitiesUpdate.prototype.survey = function () {
    var survey = {
        survey: {}
    };
    return survey;
};

objetosActivitiesUpdate.prototype.url = function () {
    var url = {
        url: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            externalurl: 'externalurl',
            display: 'display',
            displayoptions: 'displayoptions' //es un objeto, popupwidth, popupheight, printintro
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return url;
};

objetosActivitiesUpdate.prototype.wiki = function () {
    var wiki = {
        wiki: {
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            showdescription: 'showdescription ',
            defaultformat: 'defaultformat',
            forceformat: 'forceformat'
        },
        course_modules: {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        }
    };
    return wiki;
};

objetosActivitiesUpdate.prototype.workshop = function () {
    var workshop = {
        workshop: {
            workshopid: 'id',
            instance: 'id',
            name: 'name',
            intro: 'intro',
            introformat: 'introformat',
            showdescription: 'showdescription ',
            strategy: 'strategy',
            grade: 'grade',
            gradinggrade: 'gradinggrade',
            gradedecimals: 'gradedecimals',
            /*'instructauthorseditor[text]': 'instructauthors',
            'instructauthorseditor[format]': 'instructauthorsformat',*/
            instructauthors: 'instructauthors',
            instructauthorsformat: 'instructauthorsformat',
            nattachments: 'nattachments',
            submissionfiletypes: 'submissionfiletypes',
            maxbytes: 'maxbytes',
            latesubmissions: 'latesubmissions',
            instructreviewers: 'instructreviewers',
            instructreviewersformat: 'instructreviewersformat',
            /*"instructreviewerseditor[text]": 'instructreviewers',
            "instructreviewerseditor[format]": 'instructreviewersformat',*/
            useselfassessment: 'useselfassessment',
            overallfeedbackmode: 'overallfeedbackmode',
            overallfeedbackfiles: 'overallfeedbackfiles',
            overallfeedbackfiletypes: 'overallfeedbackfiletypes',
            overallfeedbackmaxbytes: 'overallfeedbackmaxbytes',
            conclusion: 'conclusion',
            conclusionformat: 'conclusionformat',
            /*"conclusioneditor[text]": 'conclusion',
            "conclusioneditor[format]": 'conclusionformat',*/
            useexamples: 'useexamples',
            examplesmode: 'examplesmode'
        },



    };
    if (window.location.href.indexOf('layouts/modedit.php') != -1) {
        workshop.grade_items = {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            submissiongradepass: 'gradepass',
            gradecategory: 'categoryid',
            grade: 'grademax',
            gradinggradecategory: 'categoryid',
            gradinggrade: 'grademax',
            gradinggradepass: 'gradepass',
        };
        workshop.course_modules = {
            coursemodule: 'id',
            course: 'course',
            module: 'module',
            instance: 'instance',
            workshopid: 'instance',
            showdescription: 'showdescription',
            visible: 'visible', //visibleold
            cmidnumber: 'idnumber',
            groupmode: 'groupmode',
            groupingid: 'groupingid',
            availabilityconditionsjson: 'availability'
        };
    } else
        ///mod/workshop/editform.php?
        if (window.location.href.indexOf('workshop/editform.php') != -1) {
            workshop.workshopform_accumulative = {
                workshopid: 'workshopid',
                dimensionid__idx_0: 'id',
                "description__idx_0_editor[text]": 'description_0',
                "description__idx_0_editor[format]": 'descriptionformat_0',
                'grade__idx_0[modgrade_type]': 'grade', //ninguna: 0, 
                'grade__idx_0[modgrade_scale]': 'grade', //cunatitativa completa: -3, defaut:-2,  separate: -1
                'grade__idx_0[modgrade_point]': 'grade', //puntuación: valor
                'weight__idx_0': 'weight',

                dimensionid__idx_1: 'id',
                "description__idx_1_editor[text]": 'description_1',
                "description__idx_1_editor[format]": 'descriptionformat_1',
                'grade__idx_1[modgrade_type]': 'grade', //ninguna: 0, 
                'grade__idx_1[modgrade_scale]': 'grade', //cunatitativa completa: -3, defaut:-2,  separate: -1
                'grade__idx_1[modgrade_point]': 'grade', //puntuación: valor
                'weight__idx_1': 'weight',

                dimensionid__idx_2: 'id',
                "description__idx_2_editor[text]": 'description_2',
                "description__idx_2_editor[format]": 'descriptionformat_2',
                'grade__idx_2[modgrade_type]': 'grade', //ninguna: 0, 
                'grade__idx_2[modgrade_scale]': 'grade', //cunatitativa completa: -3, defaut:-2,  separate: -1
                'grade__idx_2[modgrade_point]': 'grade', //puntuación: valor
                'weight__idx_2': 'weight'
            };
            workshop.workshopform_comments = {
                workshopid: 'workshopid',
                dimensionid__idx_0: 'dimensionid__idx_0',
                "description__idx_0_editor[text]": 'description_0',
                "description__idx_0_editor[format]": 'descriptionformat_0',

                dimensionid__idx_1: 'dimensionid__idx_1',
                "description__idx_1_editor[text]": 'description_1',
                "description__idx_1_editor[format]": 'descriptionformat_1',

                dimensionid__idx_2: 'dimensionid__idx_2',
                "description__idx_2_editor[text]": 'description_2',
                "description__idx_2_editor[format]": 'descriptionformat_2',

                dimensionid__idx_3: 'dimensionid__idx_3',
                "description__idx_3_editor[text]": 'description_3',
                "description__idx_3_editor[format]": 'descriptionformat_3',

                dimensionid__idx_4: 'dimensionid__idx_4',
                "description__idx_4_editor[text]": 'description_4',
                "description__idx_4_editor[format]": 'descriptionformat_4',
            };

            workshop.workshopform_numerrors = {
                workshopid: 'workshopid',
                dimensionid__idx_0: 'dimensionid__idx_0',
                "description__idx_0_editor[text]": 'description',
                "description__idx_0_editor[format]": 'descriptionformat',
                weight__idx_0: 'weight',

                dimensionid__idx_1: 'dimensionid__idx_1',
                "description__idx_1_editor[text]": 'description',
                "description__idx_1_editor[format]": 'descriptionformat',
                weight__idx_1: 'weight',

                dimensionid__idx_2: 'dimensionid__idx_2',
                "description__idx_2_editor[text]": 'description',
                "description__idx_2_editor[format]": 'descriptionformat',
                weight__idx_2: 'weight',
            };

            workshop.workshopform_rubric = {
                workshopid: 'workshopid',
                dimensionid__idx_0: 'dimensionid__idx_0',
                "description__idx_0_editor[text]": 'description',
                "description__idx_0_editor[format]": 'descriptionformat',

                dimensionid__idx_1: 'dimensionid__idx_1',
                "description__idx_1_editor[text]": 'description',
                "description__idx_1_editor[format]": 'descriptionformat',

                dimensionid__idx_2: 'dimensionid__idx_2',
                "description__idx_2_editor[text]": 'description',
                "description__idx_2_editor[format]": 'descriptionformat',
            };
        } else if (window.location.href.indexOf('workshop/allocation.php') != -1) {
        workshop.workshopallocation_scheduled = {
            cmid: 'workshopid',
            //es objeto settings   ///mod/workshop/allocation.php?cmid=1502&method=random
            numofreviews: 'settings',
            numper: 'settings',
            excludesamegroup: 'settings',
            removecurrent: 'settings',
            assesswosubmission: 'settings',
            addselfassessment: 'settings',
            enablescheduled: 'enabled', ///mod/workshop/allocation.php?cmid=1502&method=scheduled

        };
    } else {



        workshop.workshopform_numerrors_map = {
            workshopid: 'workshopid',
            dimensionid__idx_0: 'nonegative',
            map__idx_0: 'grade',

            dimensionid__idx_1: 'nonegative',
            map__idx_1: 'grade',

            dimensionid__idx_2: 'nonegative',
            map__idx_2: 'grade'
        };

        workshop.workshopform_rubric_config = {
            workshopid: 'workshopid',
            config_layout: 'layout',
        };

        workshop.workshopform_rubric_levels = { //SELECT * FROM COLUMNS WHERE TABLE_SCHEMA = 'moodle_mdl_uvd' AND COLUMN_NAME LIKE '%enablescheduled%';
            workshopid: 'workshopid',
            dimensionid__idx_0: 'dimensionid',
            levelid__idx_0__idy_0: 'id',
            "definition__idx_0__idy_0": 'description',
            "description__idx_0_editor[format]": 'descriptionformat',
            'grade__idx_0__idy_0': 'grade',

            dimensionid__idx_1: 'dimensionid',
            levelid__idx_1__idy_1: 'id',
            "definition__idx_1__idy_1": 'description',
            "description__idx_1_editor[format]": 'descriptionformat',
            'grade__idx_1__idy_1': 'grade',

            dimensionid__idx_2: 'dimensionid',
            levelid__idx_2__idy_2: 'id',
            "definition__idx_2__idy_2": 'description',
            "description__idx_2_editor[format]": 'descriptionformat',
            'grade__idx_2__idy_2': 'grade',
        };
    }
    return workshop;
};

objetosActivitiesUpdate.prototype.curso = function (id_course) {

    var curso = {
        course: {
            id: id_course,
            courseid: 'courseid',
        }
    };

    if (window.location.href.indexOf('local/backup_course/update/layouts/category') != -1 || window.location.href.indexOf('/local/backup_course/update/layouts/index') != -1) {
        curso.grade_categories = {
            id: 'id', // si es vacio es un insert
            course: id_course,
            fullname: 'fullname',
            aggregation: 'aggregation',
            aggregateoutcomes: 'aggregateoutcomes',
            aggregateonlygraded: 'aggregateonlygraded',
            droplow: 'droplow'
        };
        curso.grade_items = {
            course: id_course,

        };

    } else if (window.location.href.indexOf('local/backup_course/update/layouts/edit.php') != -1) {

        curso.course = {
            id: id_course,
            courseid: 'id',
            //startdate: 'startdate',
            //enddate: 'enddate',
            summaryformat: 'summaryformat',
            summary: 'summary',
            format: 'format',
            numsections: 'numsections',
            addcourseformatoptionshere: 'addcourseformatoptionshere',
            showgrades: 'showgrades',
            showreports: 'showreports',
            lang: 'lang',
            maxbytes: 'maxbytes',
            enablecompletion: 'enablecompletion',
            groupmode: 'groupmode',
            groupmodeforce: 'groupmodeforce',
            defaultgroupingid: 'defaultgroupingid',
            newsitems: 'newsitems',
            //timemodified: 'timemodified',
        };

    } else if (window.location.href.indexOf('local/backup_course/update/layouts/group/group.php') != -1) {

        curso.groups = {
            id: 'id', // si es vacio es un insert
            name: 'name',
            courseid: 'courseid',
            enrolmentkey: 'enrolmentkey',
            idnumber: 'idnumber',
            description: 'description',
            descriptionformat: 'descriptionformat',
            hidepicture: 'hidepicture'
        };
    } else if (window.location.href.indexOf('local/backup_course/update/layouts/group/grouping.php') != -1) {
        curso.groupings = {
            id: 'id', // si es vacio es un insert
            name: 'name',
            courseid: 'courseid',
            idnumber: 'idnumber',
            descriptiontrust: 'description',
            description: 'descriptionformat'
        };
    } else if (window.location.href.indexOf('local/backup_course/update/layouts/group/assign.php') != -1) {
        curso.groupings_groups = {
                addselect: 'addselect', // id de los grupos // hay que enviarle el id de la agrupacion
                courseid: 'courseid'
            },
            curso.groupings = {
                id: 'id', // si es vacio es un insert
                name: 'name',
                courseid: 'courseid',
                idnumber: 'idnumber',
                descriptiontrust: 'description',
                description: 'descriptionformat'
            };

        //banco de preguntas    
    } else if (window.location.href.indexOf('local/backup_course/update/layouts/question/category.php') != -1) {
        curso.question_categories = { // /question/category.php
            id: 'id',
            name: 'name',
            parent: 'parent',
            infoformat: 'infoformat',
            info: 'info'
        };
    } else if (window.location.href.indexOf('local/backup_course/update/layouts/question/bank/category.php') != -1) {
        curso.question_categories = {
            id: 'id',
            name: 'name',
            contextid: 'contextid',
            info: 'info',
            infoformat: 'infoformat',
            stamp: 'stamp',
            parent: 'parent',
            sortorder: 'sortorder',
            idnumber: 'idnumber'
        };
    } else if (window.location.href.indexOf('local/backup_course/update/layouts/question/question.php') != -1) {
        curso.question = {
            id: 'id',
            name: 'name',
            category: 'category',
            qtype: 'qtype',
            defaultmark: 'defaultmark',
            penalty: 'penalty',
        };
    } else if (window.location.href.indexOf('local/backup_course/update/layouts/quiz/edit.') != -1) {
        curso.question_in_quiz = {
            id: 'id',
            course: id_course,
            questionid: 'questionid',
            quizid: 'quizid',
        };

    } else if (window.location.href.indexOf('local/backup_course/update/layouts/quiz/editrandom.') != -1) {
        curso.quiz_slots = {
            id: 'id',
            course: id_course,
            lotid: 'lotid',
            quizid: 'quizid',
        };

    } else if (window.location.href.indexOf('local/backup_course/update/layouts/grade/edit/item') != -1) {

        curso.grade_items = {
            name: 'itemname',
            modulename: 'itemmodule',
            instance: 'iteminstance',
            course: 'courseid',
            gradepass: 'gradepass',
            gradecat: 'categoryid',
            submissiongradepass: 'gradepass',
            gradecategory: 'categoryid',
            grade: 'grademax',
            gradinggradecategory: 'categoryid',
            gradinggrade: 'grademax',
            gradinggradepass: 'gradepass',
        }

    } else if (window.location.href.indexOf('local/backup_course/update/layouts/grade/edit/category') != -1) {

        curso.grade_categories = {
            id:'id',
            courseid: 'courseid',
            parent: 'parent',
            depth: 'depth',
            path: 'path',
            fullname: 'fullname',
            aggregation: 'agreggation',
            keephigh: 'keephigh',
            droplow: 'droplow',
            aggregateonlygraded: 'aggregateonlygraded',
            aggregateoutcomes: 'aggregateoutcomes',
            timecreated: 'timecreated',
            timemodified: 'timemodified',
            hidden: 'hidden',
            grade_items: {
                name: 'itemname',
                modulename: 'itemmodule',
                instance: 'iteminstance',
                course: 'courseid',
                gradepass: 'gradepass',
                gradecat: 'categoryid',
                submissiongradepass: 'gradepass',
                gradecategory: 'categoryid',
                grade: 'grademax',
                gradinggradecategory: 'categoryid',
                gradinggrade: 'grademax',
                gradinggradepass: 'gradepass',
            }
        };


    }

    return curso;

};

var json = {
    intro: 'ssssssssss',
    introformat: 'dfdsfdsfds'
};

objetosActivitiesUpdate.prototype.insertarModal = function (id) {

    var contenidoHTML = `
    <div id="miModal${id}" class="modalN">
        <div class="modal-contenido">
            <span class="modal-cerrar" id="modal-cerrar${id}" >&times;</span>
            <h2>Modal Título</h2>
            <p>Este es el contenido del modal.</p>
        </div>
    </div>`;

    document.body.innerHTML += contenidoHTML;

};

objetosActivitiesUpdate.prototype.agregarCSS = function () {
    // Crear un elemento <style>
    var estilo = document.createElement("style");

    // Agregar el código CSS deseado
    estilo.innerHTML = `
    .modalN {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
      }
      
      .modal-contenido {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
      }
      
      .modal-cerrar {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
      }

    `;

    // Agregar el elemento <style> al elemento <head>
    document.head.appendChild(estilo);
};