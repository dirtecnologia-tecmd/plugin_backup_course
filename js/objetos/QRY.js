
function objetosQuery(){} 

objetosQuery.prototype.obQ01 = function(){
    /*
     * 
     * @type Object
     * Objeto para listar los nodos
     * Todos los datos son parametros 
     * Envío de datos para listar los nodos
     */
    var QRY_Q01 = new Object();
        QRY_Q01.type = 'QRY';
        QRY_Q01.key = 'Q01';
        QRY_Q01.ws_url = '/class.query.php';

    return QRY_Q01;
};

objetosQuery.prototype.obQ02 = function(data){
    /*
     * 
     * @type Object
     * Objeto para listar cursos
     * Todos los datos son parametros 
     * Envío de datos para listar los cursos
     */
    var QRY_Q02 = new Object();
        QRY_Q02.type = 'QRY';
        QRY_Q02.key = 'Q02';
        QRY_Q02.id_nodo = data[0].value;
        QRY_Q02.search = data[4].value;
        QRY_Q02.ws_url = '/class.query.php';

    return QRY_Q02;
};

objetosQuery.prototype.obQ03 = function(id, id_nodo){
    /*
     * 
     * @type Object
     * Objeto para listar las secciones con activiades
     * Todos los datos son parametros 
     * Envío de datos para listar los cursos
     */
    var QRY_Q03 = new Object();
        QRY_Q03.type = 'QRY';
        QRY_Q03.key = 'Q03';
        QRY_Q03.id_course = id;
        QRY_Q03.id_nodo = id_nodo;
        QRY_Q03.ws_url = '/class.query.php';

    return QRY_Q03;
};

objetosQuery.prototype.obQ04 = function(){
    /*
     * 
     * @type Object
     * Objeto para relacionar las actividades del curso padre con las del hijo
     * Todos los datos son parametros 
     */
    var QRY_Q04 = new Object();
        QRY_Q04.type = 'QRY';
        QRY_Q04.key = 'Q04';
        QRY_Q04.data_obj = {
            cursos:{
                id_padre: '',
                id_hijo: '',
                sections: {
                    id_sec_padre: '',
                    id_sec_hijo: '',
                    activities: {
                        table: '',
                        id_acti_p: '',
                        id_acti: '',
                        id_como_p: '',
                        id_como: '',
                        info_actividad: {
                            nameTable:{
                                p:'',
                                h:''
                            },
                            nameTable1:{
                                p:'',
                                h:''
                            },
                            nameTable2:{
                                p:'',
                                h:''
                            }
                        }
                    }
                }
            }
        };

    return QRY_Q04;
};
objetosQuery.prototype.obQ05 = function(){
    /*
     * @type Object
     * Objeto para el contenido del curso
     * Todos los datos son parametros 
     * Envío de datos el contenido del curso
     */
    var QRY_Q05 = new Object();
        QRY_Q05.type = 'QRY';
        QRY_Q05.key = 'Q05';
        QRY_Q05.ack = '';
        QRY_Q05.id_course = 4;
        QRY_Q05.id_nodo = 2;
        QRY_Q05.ws_url = '/class.update.php';
        QRY_Q05.data_child = {
            sections: {
                section: {
                    id: '',
                    course: '',
                    section: '',
                    name: '',
                    summary: '',
                    summaryformat: '',
                    sequence: '',
                    visible: '',
                    availability: '',
                    activities : {
                        assigns:{
                            assign: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                alwaysshowdescription: '',
                                nosubmissions: '',
                                submissiondrafts: '',
                                sendnotifications: '',
                                sendlatenotifications: '',
                                duedate: '',
                                allowsubmissionsfromdate: '',
                                grade: '',
                                timemodified: '',
                                requiresubmissionstatement: '',
                                completionsubmit: '',
                                cutoffdate: '',
                                teamsubmission: '',
                                requireallteammemberssubmit: '',
                                teamsubmissiongroupingid: '',
                                blindmarking: '',
                                revealidentities: '',
                                attemptreopenmethod: '',
                                maxattempts: '',
                                markingworkflow: '',
                                markingallocation: '',
                                sendstudentnotifications: '',
                                preventsubmissionnotingroup: '',
                                assign_plugin_config: {
                                    id: '',
                                    assignment: '',
                                    plugin: '',
                                    subtype: '',
                                    name: '',
                                    value: '',
                                    context:{
                                        id: '',
                                        contextlevel: '',
                                        instanceid: '',
                                        path: '',
                                        depth: '',
                                        grading_areas:{
                                            id: '',
                                            contextid: '',
                                            component: '',
                                            areaname: '',
                                            activemethod: '',
                                            grading_definitions:{
                                                id: '',
                                                areaid: '',
                                                method: '',
                                                name: '',
                                                description: '',
                                                descriptionformat: '',
                                                status: '',
                                                copiedfromid: '',
                                                timecreated: '',
                                                usercreated: '',
                                                timemodified: '',
                                                usermodified: '',
                                                timecopied: '',
                                                options: '',
                                                gradingform_rubric_criteria:{
                                                    id: '',
                                                    definitionid: '',
                                                    sortorder: '',
                                                    description: '',
                                                    descriptionformat: '',
                                                    gradingform_rubric_levels:{
                                                        id: '',
                                                        criterionid: '',
                                                        score: '',
                                                        definition: '',
                                                        definitionformat: '',
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                            },
                        },
                        assignments: {
                            assignment:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                assignmenttype: '',
                                resubmit: '',
                                preventlate: '',
                                emailteachers: '',
                                var1: '',
                                var2: '',
                                var3: '',
                                var4: '',
                                var5: '',
                                maxbytes: '',
                                timedue: '',
                                timeavailable: '',
                                grade: '',
                                timemodified: '',
                            },
                        },
                        attendances: {
                            attendance:{
                                id: '',
                                course: '',
                                name: '',
                                timemodified: '',
                                grade: '',
                                intro: '',
                                introformat: '',
                                attendance_statuses:{
                                    id: '',
                                    attendanceid: '',
                                    acronym: '',
                                    description: '',
                                    grade: '',
                                    studentavailability: '',
                                    setunmarked: '',
                                    visible: '',
                                    deleted: '',
                                    setnumber: '',
                                },
                                attendance_sessions:{
                                    id: '',
                                    attendanceid: '',
                                    groupid: '',
                                    sessdate: '',
                                    duration: '',
                                    lasttaken: '',
                                    lasttakenby: '',
                                    timemodified: '',
                                    description: '',
                                    descriptionformat: '',
                                    studentscanmark: '',
                                    studentpassword: '',
                                    subnet: '',
                                    automark: '',
                                    automarkcompleted: '',
                                    statusset: '',
                                    caleventid: '',
                                }
                            }
                        },
                        books: {
                            book: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                numbering: '',
                                navstyle: '',
                                customtitles: '',
                                revision: '',
                                timecreated: '',
                                timemodified: '',
                                book_chapters:{
                                    id: '',
                                    bookid: '',
                                    pagenum: '',
                                    subchapter: '',
                                    title: '',
                                    content: '',
                                    contentformat: '',
                                    hidden: '',
                                    timecreated: '',
                                    timemodified: '',
                                    importsrc: '',
                                }
                            },
                        },
                        certificates: {
                            certificate: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                emailteachers: '',
                                emailothers: '',
                                savecert: '',
                                reportcert: '',
                                delivery: '',
                                requiredtime: '',
                                certificatetype: '',
                                orientation: '',
                                borderstyle: '',
                                bordercolor: '',
                                printwmark: '',
                                printdate: '',
                                datefmt: '',
                                printnumber: '',
                                printgrade: '',
                                gradefmt: '',
                                printoutcome: '',
                                printhours: '',
                                printteacher: '',
                                customtext: '',
                                printsignature: '',
                                printseal: '',
                                timecreated: '',
                                timemodified: '',
                                certificate_issues: {
                                    id: '',
                                    userid: '',
                                    certificateid: '',
                                    code: '',
                                    timecreated: '',
                                }
                            },
                        },
                        chats: {
                            chat: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                keepdays: '',
                                studentlogs: '',
                                chattime: '',
                                schedule: '',
                                timemodified: '',
                            },
                        },
                        choices: {
                            choice: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                publish: '',
                                showresults: '',
                                display: '',
                                allowupdate: '',
                                allowmultiple: '',
                                showunanswered: '',
                                includeinactive: '',
                                limitanswers: '',
                                timeopen: '',
                                timeclose: '',
                                showpreview: '',
                                timemodified: '',
                                completionsubmit: '',
                                choice_options: {
                                    id: '',
                                    choiceid: '',
                                    userid: '',
                                    optionid: '',
                                    timemodified: '',
                                },
                            },
                        },
                        choicegroups: {
                            choicegroup:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                publish: '',
                                multipleenrollmentspossible: '',
                                showresults: '',
                                display: '',
                                allowupdate: '',
                                showunanswered: '',
                                limitanswers: '',
                                timeopen: '',
                                timeclose: '',
                                timemodified: '',
                                completionsubmit: '',
                                sortgroupsby: '',
                                choicegroup_options: {
                                    id: '',
                                    choicegroupid: '',
                                    groupid: '',
                                    maxanswers: '',
                                    timemodified: '',
                                }
                            },
                        },
                        collaborates :{
                            collaborate: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                sessionid: '',
                                timestart: '',
                                duration: '',
                                timeend: '',
                                boundaryminutes: '',
                                timecreated: '',
                                timemodified: '',
                                grade: '',
                                completionlaunch: '',
                                guestaccessenabled: '',
                                guestrole: '',
                                guesturl: '',
                            },
                        },
                        datas: {
                            data: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                comments: '',
                                timeavailablefrom: '',
                                timeavailableto: '',
                                timeviewfrom: '',
                                timeviewto: '',
                                requiredentries: '',
                                requiredentriestoview: '',
                                maxentries: '',
                                rssarticles: '',
                                singletemplate: '',
                                listtemplate: '',
                                listtemplateheader: '',
                                listtemplatefooter: '',
                                addtemplate: '',
                                rsstemplate: '',
                                csstemplate: '',
                                jstemplate: '',
                                asearchtemplate: '',
                                approval: '',
                                manageapproved: '',
                                scale: '',
                                assessed: '',
                                assesstimestart: '',
                                assesstimefinish: '',
                                defaultsort: '',
                                defaultsortdir: '',
                                editany: '',
                                notification: '',
                                timemodified: '',
                                data_fields: {
                                    id: '',
                                    dataid: '',
                                    type: '',
                                    name: '',
                                    description: '',
                                    required: '',
                                    param1: '',
                                    param2: '',
                                    param3: '',
                                    param4: '',
                                    param5: '',
                                    param6: '',
                                    param7: '',
                                    param8: '',
                                    param9: '',
                                    param10: '',
                                }
                            },
                        },
                        feedbacks: {
                            feedback:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                anonymous: '',
                                email_notification: '',
                                multiple_submit: '',
                                autonumbering: '',
                                site_after_submit: '',
                                page_after_submit: '',
                                page_after_submitformat: '',
                                publish_stats: '',
                                timeopen: '',
                                timeclose: '',
                                timemodified: '',
                                completionsubmit: '',
                                feedback_item: {
                                    id: '',
                                    feedback: '',
                                    template: '',
                                    name: '',
                                    label: '',
                                    presentation: '',
                                    typ: '',
                                    hasvalue: '',
                                    position: '',
                                    required: '',
                                    dependitem: '',
                                    dependvalue: '',
                                    options: '',
                                }
                            },
                        },
                        /*files:{
                            files:{
                                id: '',
                                contenthash: '',
                                pathnamehash: '',
                                contextid: '',
                                component: '',
                                filearea: '',
                                itemid: '',
                                filepath: '',
                                filename: '',
                                userid: '',
                                filesize: '',
                                mimetype: '',
                                status: '',
                                source: '',
                                author: '',
                                license: '',
                                timecreated: '',
                                timemodified: '',
                                sortorder: '',
                                referencefileid: '',
                            },
                        },*/
                        folders:{
                            folder: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                revision: '',
                                timemodified: '',
                                display: '',
                                showexpanded: '',
                                showdownloadfolder: '',
                                forcedownload: '',
                                files:{
                                    
                                }
                            }
                        },
                        forums: {
                            forum:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                assessed: '',
                                assesstimestart: '',
                                assesstimefinish: '',
                                scale: '',
                                maxbytes: '',
                                maxattachments: '',
                                forcesubscribe: '',
                                trackingtype: '',
                                rsstype: '',
                                rssarticles: '',
                                timemodified: '',
                                warnafter: '',
                                blockafter: '',
                                blockperiod: '',
                                completiondiscussions: '',
                                completionreplies: '',
                                completionposts: '',
                                displaywordcount: '',
                                forum_discussions: {
                                    id: '',
                                    course: '',
                                    forum: '',
                                    name: '',
                                    firstpost: '',
                                    userid: '',
                                    groupid: '',
                                    assessed: '',
                                    timemodified: '',
                                    usermodified: '',
                                    timestart: '',
                                    timeend: '',
                                    pinned: '',
                                    forum_posts:{
                                        id: '',
                                        discussion: '',
                                        parent: '',
                                        userid: '',
                                        created: '',
                                        modified: '',
                                        mailed: '',
                                        subject: '',
                                        message: '',
                                        messageformat: '',
                                        messagetrust: '',
                                        attachment: '',
                                        totalscore: '',
                                        mailnow: '',
                                    }
                                }
                            },
                        },
                        games:{
                            game:{
                                id: '',
                                name: '',
                                course: '',
                                sourcemodule: '',
                                timeopen: '',
                                timeclose: '',
                                quizid: '',
                                glossaryid: '',
                                glossarycategoryid: '',
                                questioncategoryid: '',
                                bookid: '',
                                gamekind: '',
                                param1: '',
                                param2: '',
                                param3: '',
                                param4: '',
                                param5: '',
                                param6: '',
                                param7: '',
                                param8: '',
                                param9: '',
                                param10: '',
                                shuffle: '',
                                timemodified: '',
                                gameinputid: '',
                                toptext: '',
                                bottomtext: '',
                                grademethod: '',
                                grade: '',
                                decimalpoints: '',
                                popup: '',
                                review: '',
                                attempts: '',
                                glossaryid2: '',
                                glossarycategoryid2: '',
                                language: '',
                                subcategories: '',
                                maxattempts: '',
                                userlanguage: '',
                                disablesummarize: '',
                                glossaryonlyapproved: '',
                                completionattemptsexhausted: '',
                                completionpass: '',
                            },
                        },
                        glossarys: {
                            glossary:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                allowduplicatedentries: '',
                                displayformat: '',
                                mainglossary: '',
                                showspecial: '',
                                showalphabet: '',
                                showall: '',
                                allowcomments: '',
                                allowprintview: '',
                                usedynalink: '',
                                defaultapproval: '',
                                approvaldisplayformat: '',
                                globalglossary: '',
                                entbypage: '',
                                editalways: '',
                                rsstype: '',
                                rssarticles: '',
                                assessed: '',
                                assesstimestart: '',
                                assesstimefinish: '',
                                scale: '',
                                timecreated: '',
                                timemodified: '',
                                completionentries: '',
                                glossary_entries: {
                                    id: '',
                                    glossaryid: '',
                                    userid: '',
                                    concept: '',
                                    definition: '',
                                    definitionformat: '',
                                    definitiontrust: '',
                                    attachment: '',
                                    timecreated: '',
                                    timemodified: '',
                                    teacherentry: '',
                                    sourceglossaryid: '',
                                    usedynalink: '',
                                    casesensitive: '',
                                    fullmatch: '',
                                    approved: '',
                                }
                            },
                        },
                        groupselects:{
                            groupselect:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                targetgrouping: '',
                                maxmembers: '',
                                timeavailable: '',
                                timedue: '',
                                timecreated: '',
                                timemodified: '',
                                hidefullgroups: '',
                                hidesuspendedstudents: '',
                                hidegroupmembers: '',
                                deleteemptygroups: '',
                                studentcancreate: '',
                                minmembers: '',
                                assignteachers: '',
                                studentcansetdesc: '',
                                showassignedteacher: '',
                                studentcansetenrolmentkey: '',
                                studentcansetgroupname: '',
                                notifyexpiredselection: '',
                                supervisionrole: '',
                                maxgroupmembership: '',
                                studentcanjoin: '',
                                studentcanleave: '',
                            },
                        },
                        imscps: {
                            imscp: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                revision: '',
                                keepold: '',
                                structure: '',
                                timemodified: '',
                            },
                        },
                        journals: {
                            journal: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                days: '',
                                grade: '',
                                timemodified: '',
                            },
                        },
                        labels:{
                            label:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                timemodified: '',
                            },
                        },
                        lessons:{
                            lesson: {
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                practice: '',
                                modattempts: '',
                                usepassword: '',
                                password: '',
                                dependency: '',
                                conditions: '',
                                grade: '',
                                custom: '',
                                ongoing: '',
                                usemaxgrade: '',
                                maxanswers: '',
                                maxattempts: '',
                                review: '',
                                nextpagedefault: '',
                                feedback: '',
                                minquestions: '',
                                maxpages: '',
                                timelimit: '',
                                retake: '',
                                activitylink: '',
                                mediafile: '',
                                mediaheight: '',
                                mediawidth: '',
                                mediaclose: '',
                                slideshow: '',
                                width: '',
                                height: '',
                                bgcolor: '',
                                displayleft: '',
                                displayleftif: '',
                                progressbar: '',
                                available: '',
                                deadline: '',
                                timemodified: '',
                                completionendreached: '',
                                completiontimespent: '',
                                lesson_pages: {
                                    id: '',
                                    lessonid: '',
                                    prevpageid: '',
                                    nextpageid: '',
                                    qtype: '',
                                    qoption: '',
                                    layout: '',
                                    display: '',
                                    timecreated: '',
                                    timemodified: '',
                                    title: '',
                                    contents: '',
                                    contentsformat: '',
                                },
                                lesson_answers:{
                                    id: '',
                                    lessonid: '',
                                    pageid: '',
                                    jumpto: '',
                                    grade: '',
                                    score: '',
                                    flags: '',
                                    timecreated: '',
                                    timemodified: '',
                                    answer: '',
                                    answerformat: '',
                                    response: '',
                                    responseformat: '',
                                }
                            },
                        },
                        ltis:{
                            lti:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                timecreated: '',
                                timemodified: '',
                                typeid: '',
                                toolurl: '',
                                securetoolurl: '',
                                instructorchoicesendname: '',
                                instructorchoicesendemailaddr: '',
                                instructorchoiceallowroster: '',
                                instructorchoiceallowsetting: '',
                                instructorcustomparameters: '',
                                instructorchoiceacceptgrades: '',
                                grade: '',
                                launchcontainer: '',
                                resourcekey: '',
                                password: '',
                                debuglaunch: '',
                                showtitlelaunch: '',
                                showdescriptionlaunch: '',
                                servicesalt: '',
                                icon: '',
                                secureicon: '',
                            },
                        },
                        pages: {
                            page:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                content: '',
                                contentformat: '',
                                legacyfiles: '',
                                legacyfileslast: '',
                                display: '',
                                displayoptions: '',
                                revision: '',
                                timemodified: '',
                            },
                        },
                        quizs: {
                            quiz:{
                               id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '',
                                timeopen: '', 
                                timeclose: '', 
                                timelimit: '', 
                                overduehandling: '', 
                                graceperiod: '', 
                                preferredbehaviour: '', 
                                canredoquestions: '', 
                                attempts: '', 
                                attemptonlast: '', 
                                grademethod: '', 
                                decimalpoints: '', 
                                questiondecimalpoints: '', 
                                reviewattempt: '', 
                                reviewcorrectness: '', 
                                reviewmarks: '', 
                                reviewspecificfeedback: '', 
                                reviewgeneralfeedback: '', 
                                reviewrightanswer: '', 
                                reviewoverallfeedback: '', 
                                questionsperpage: '', 
                                navmethod: '', 
                                shuffleanswers: '', 
                                sumgrades: '', 
                                grade: '', 
                                timecreated: '', 
                                timemodified: '', 
                                password: '', 
                                subnet: '', 
                                browsersecurity: '', 
                                delay1: '', 
                                delay2: '', 
                                showuserpicture: '', 
                                showblocks: '', 
                                completionattemptsexhausted: '', 
                                completionpass: '', 
                                quiz_sections:{
                                    id: '',
                                    quizid: '',
                                    firstslot: '',
                                    heading: '',
                                    shufflequestions: '',
                                },
                                quiz_feedback: {
                                    id: '',
                                    quizid: '',
                                    /* feedbacktext: '', */
                                    feedbacktextformat: '',
                                    mingrade: '',
                                    maxgrade: '',
                                },
                                quiz_slots: {
                                    id: '',
                                    slot: '',
                                    quizid: '',
                                    page: '',
                                    requireprevious: '',
                                   /*  questionid: '', */
                                    maxmark: '',
                                },
                                question_categories: {
                                    id: '',
                                    name: '',
                                    contextid: '',
                                    info: '',
                                    infoformat: '',
                                    stamp: '',
                                    parent: '',
                                    sortorder: '',
                                    context:{
                                        id: '',
                                        contextlevel: '',
                                        instanceid: '',
                                        path: '',
                                        depth: '',
                                    },
                                    question: {
                                        id: '',
                                        category: '',
                                        parent: '',
                                        name: '',
                                        questiontext: '',
                                        questiontextformat: '',
                                        generalfeedback: '',
                                        generalfeedbackformat: '',
                                        defaultmark: '',
                                        penalty: '',
                                        qtype: '',
                                        length: '',
                                        stamp: '',
                                        version: '',
                                        hidden: '',
                                        timecreated: '',
                                        timemodified: '',
                                        createdby: '',
                                        modifiedby: '',
                                        
                                        question_answers:{
                                            id: '',
                                            question: '',
                                            answer: '',
                                            answerformat: '',
                                            fraction: '',
                                            feedback: '',
                                            feedbackformat: '',
                                        },
                                        qtype_ddimageortext: {},
                                        qtype_ddimageortext_drags: {},
                                        qtype_ddimageortext_drops: {},
                                        qtype_ddmarker: {},
                                        qtype_ddmarker_drags: {},
                                        qtype_ddmarker_drops: {},
                                        qtype_essay_options: {},
                                        qtype_match_options: {},
                                        qtype_match_subquestions: {},
                                        qtype_randomsamatch_options: {},
                                        qtype_shortanswer_options: {},
                                    },
                                },
                                
                            },
                        },
                        resources:{
                            resource:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                tobemigrated: '', 
                                legacyfiles: '', 
                                legacyfileslast: '', 
                                display: '', 
                                displayoptions: '', 
                                filterfiles: '', 
                                revision: '', 
                                timemodified: '',
                            },
                        },
                        scorms:{
                            scorm:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                version: '',
                                maxgrade: '',
                                grademethod: '',
                                whatgrade: '',
                                maxattempt: '',
                                forcecompleted: '',
                                forcenewattempt: '',
                                lastattemptlock: '',
                                masteryoverri: '',
                                displayattemptstatus: '',
                                displaycoursestructure: '',
                                updatefreq: '',
                                sha1hash: '',
                                md5hash: '',
                                revision: '',
                                launch: '',
                                skipview: '',
                                hidebrowse: '',
                                hidetoc: '',
                                nav: '',
                                navpositionleft: '',
                                navpositiontop: '',
                                auto: '',
                                popup: '',
                                options: '',
                                width: '',
                                height: '',
                                timeopen: '',
                                timeclose: '',
                                timemodified: '',
                                completionstatusrequired: '',
                                completionscorerequired: '',
                                displayactivityname: '',
                                autocommit: '',
                                scorm_scoes: {
                                    id: '',
                                    scorm: '',
                                    manifest: '',
                                    organization: '',
                                    parent: '',
                                    identifier: '',
                                    launch: '',
                                    scormtype: '',
                                    title: '',
                                    sortorder: '',
                                },
                                scorm_scoes_data: {
                                    id: '',
                                    scoid: '',
                                    name: '',
                                    value: '',
                                },
                            },
                        },
                        surveys:{
                            survey:{
                                id: '',
                                course: '',
                                template: '',
                                days: '',
                                timecreated: '', 
                                timemodified: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                            },
                        },
                        urls:{
                            url:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                externalurl: '', 
                                display: '', 
                                displayoptions: '', 
                                parameters: '', 
                                timemodified: '',
                            },
                        },
                        wikis: {
                            wiki:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                timecreated: '',
                                timemodified: '',
                                firstpagetitle: '',
                                wikimode: '',
                                defaultformat: '',
                                forceformat: '',
                                editbegin: '',
                                editend: '',
                            },
                        },
                        workshops:{
                            workshop:{
                                id: '',
                                course: '',
                                name: '',
                                intro: '',
                                introformat: '', 
                                instructauthors: '',
                                instructauthorsformat: '',
                                instructreviewers: '',
                                instructreviewersformat: '',
                                timemodified: '',
                                phase: '',
                                useexamples: '',
                                usepeerassessment: '',
                                useselfassessment: '',
                                grade: '',
                                gradinggrade: '',
                                strategy: '',
                                evaluation: '',
                                gradedecimals: '',
                                nattachments: '',
                                submissionfiletypes: '',
                                latesubmissions: '',
                                maxbytes: '',
                                examplesmode: '',
                                submissionstart: '',
                                submissionend: '',
                                assessmentstart: '',
                                assessmentend: '',
                                phaseswitchassessment: '',
                                conclusion: '',
                                conclusionformat: '',
                                overallfeedbackmode: '',
                                overallfeedbackfiles: '',
                                overallfeedbackfiletypes: '',
                                overallfeedbackmaxbytes: '',
                                workshopform_accumulative: {
                                    id: '',
                                    workshopid: '',
                                    sort: '',
                                    description: '',
                                    descriptionformat: '',
                                    grade: '',
                                    weight: '',
                                }
                            },
                        },
                    },
                },
            },
        };

    return QRY_Q05;
};



objetosQuery.prototype.obQ06 = function(id,id_nodo,obj){
    /*
     * 
     * @type Object
     * Objeto para buscar los bloques, grades, items grupos del curso en el padre
     * Todos los datos son parametros 
     */
    var QRY_Q06 = new Object();
        QRY_Q06.type = 'QRY';
        QRY_Q06.key = 'Q06';
        QRY_Q06.ws_url = '/class.query.php';
        QRY_Q06.id_course = id;
        QRY_Q06.id_nodo = id_nodo;
        QRY_Q06.obj = obj;

    return QRY_Q06;
};

objetosQuery.prototype.obQ07 = function(id,id_nodo){
    /*
     * 
     * @type Object
     * Objeto para relacionar el banco de preguntas del padre con el del hijo
     * Todos los datos son parametros 
     */
    var QRY_Q07 = new Object();
        QRY_Q07.type = 'QRY';
        QRY_Q07.key = 'Q07';
        QRY_Q07.ws_url = '/class.query.php';
        QRY_Q07.id_course = id;
        QRY_Q07.id_nodo = id_nodo;

    return QRY_Q07;
};

objetosQuery.prototype.obQ09 = function(id,id_nodo){

    var QRY_Q09 = new Object();
        QRY_Q09.type = 'QRY';
        QRY_Q09.key = 'Q09';
        QRY_Q09.ws_url = '/class.query.php';
        QRY_Q09.id_course = id;
        QRY_Q09.id_nodo = id_nodo;

    return QRY_Q09;
};


objetosQuery.prototype.obQ100 = function(id,id_nodo){
    /*
     * 
     * @type Object
     * Objeto para relacionar el banco de preguntas del padre con el del hijo
     * Todos los datos son parametros 
     */
    var QRY_Q100 = new Object();
        QRY_Q100.type = 'QRY';
        QRY_Q100.key = 'Q100';
        QRY_Q100.ws_url = '/class.query.php';
        QRY_Q100.id_course = id;
        QRY_Q100.id_nodo = id_nodo;

    return QRY_Q100;
};


objetosQuery.prototype.obQ101 = function(id,id_nodo){
    /*
     * 
     * @type Object
     * Objeto para relacionar el banco de preguntas del padre con el del hijo
     * Todos los datos son parametros 
     */
    var QRY_Q101 = new Object();
        QRY_Q101.type = 'QRY';
        QRY_Q101.key = 'Q101';
        QRY_Q101.ws_url = '/class.query.php';
        QRY_Q101.id_course = id;
        QRY_Q101.id_nodo = id_nodo;

    return QRY_Q101;
};

objetosQuery.prototype.obQ102 = function(id,id_nodo){
    /*
     * 
     * @type Object
     * Objeto para relacionar el banco de preguntas del padre con el del hijo
     * Todos los datos son parametros 
     */
    var QRY_Q102 = new Object();
        QRY_Q102.type = 'QRY';
        QRY_Q102.key = 'Q102';
        QRY_Q102.ws_url = '/class.query.php';
        QRY_Q102.id_course = id;
        QRY_Q102.id_nodo = id_nodo;

    return QRY_Q102;
}



/*Documentacion para los bancos de preguntas*/
var banco_preguntas = new Object();
    banco_preguntas.question_categories = {
        id: '',
        name: '',
        contextid: '',
        info: '',
        infoformat: '',
        stamp: '',
        parent: '',
        sortorder: '',
        context:{
            id: '',
            contextlevel: '',
            instanceid: '',
            path: '',
            depth: '',
        },
        question: {
            id: '',
            category: '',
            parent: '',
            name: '',
            questiontext: '',
            questiontextformat: '',
            generalfeedback: '',
            generalfeedbackformat: '',
            defaultmark: '',
            penalty: '',
            qtype: '',
            length: '',
            stamp: '',
            version: '',
            hidden: '',
            timecreated: '',
            timemodified: '',
            createdby: '',
            modifiedby: '',
            question_answers:{
                id: '',
                question: '',
                answer: '',
                answerformat: '',
                fraction: '',
                feedback: '',
                feedbackformat: '',
            },
            qtype_ddimageortext: {},
            qtype_ddimageortext_drags: {},
            qtype_ddimageortext_drops: {},
            qtype_ddmarker: {},
            qtype_ddmarker_drags: {},
            qtype_ddmarker_drops: {},
            qtype_essay_options: {},
            qtype_match_options: {},
            qtype_match_subquestions: {},
            qtype_multichoice_options: {},
            qtype_randomsamatch_options: {},
            qtype_shortanswer_options: {},
        },
    };
/*Objeto para información en general del curso*/
var all_info_course = new Object();
    all_info_course.question_categories = {
        bloques:{
            id: '',
            blockname: '',
            parentcontextid: '',
            showinsubcontexts: '',
            requiredbytheme: '',
            pagetypepattern: '',
            subpagepattern: '',
            defaultregion: '',
            defaultweight: '',
            configdata: '',
        },
        scale: {
            id: '',
            courseid: '',
            userid: '',
            name: '',
            scale: '',
            description: '',
            descriptionformat: '',
            timemodified: '',
        },
        course_format_options: {
            id: '',
            courseid: '',
            format: '',
            sectionid: '',
            name: '',
            value: '',
        },
        grade_settings: {
            id: '',
            courseid: '',
            name: '',
            value: '',
        },
        groups: {
            id: '',
            courseid: '',
            idnumber: '',
            name: '',
            description: '',
            descriptionformat: '',
            enrolmentkey: '',
            picture: '',
            hidepicture: '',
            timecreated: '',
            timemodified: '',
        },
        groupings: {
            id: '',
            courseid: '',
            name: '',
            idnumber: '',
            description: '',
            descriptionformat: '',
            configdata: '',
            timecreated: '',
            timemodified: '',
            groupings_groups: {
                id: '',
                groupingid: '',
                groupid: '',
                timeadded: '',
            },
        },
        grade_categories: {
            id: '',
            courseid: '',
            parent: '',
            depth: '',
            path: '',
            fullname: '',
            aggregation: '',
            keephigh: '',
            droplow: '',
            aggregateonlygraded: '',
            aggregateoutcomes: '',
            timecreated: '',
            timemodified: '',
            hidden: '',
            grade_items: {
                id: '',
                courseid: '',
                categoryid: '',
                itemname: '',
                itemtype: '',
                itemmodule: '',
                iteminstance: '',
                itemnumber: '',
                iteminfo: '',
                idnumber: '',
                calculation: '',
                gradetype: '',
                grademax: '',
                grademin: '',
                scaleid: '',
                outcomeid: '',
                gradepass: '',
                multfactor: '',
                plusfactor: '',
                aggregationcoef: '',
                aggregationcoef2: '',
                sortorder: '',
                display: '',
                decimals: '',
                hidden: '',
                locked: '',
                locktime: '',
                needsupdate: '',
                weightoverride: '',
                timecreated: '',
                timemodified: '',
            },
        },
        
    };



data_obj = {
    cursos:{
        id_padre: '',
        id_hijo: '',
        sections: {
            id_sec_padre: "",
            id_sec_hijo: "",
            activities: {
                table: "quiz",
                id_acti_p: "110",
                id_acti: "768",
                id_como_p: "924",
                id_como: "4476",
                info_actividad: {
                    
                }
            }
        }
    }
};

var OQRY = new objetosQuery();