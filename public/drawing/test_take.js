// var TestTake = {
//
//     i : null,
//     heartBeatInterval : null,
//     heartBeatCallback : null,
//     showProgress : true,
//     discussingQuestionId : null,
//     discussingAllDiscussed : true,
//     active : false,
//     studentsPresent : false,
//     isVisible : true,
//     checkIframe : false,
//     alert: false,
//
//     startHeartBeat : function(callback, interval) {
//         if(callback == 'active'){
//             // console.log('startheartbeat');
//             if(!TestTake.active) {
//                 TestTake.atTestStart();
//             }
//             else{
//                 this.markBackground();
//             }
//         }
//
//         var intervalInSeconds = 3*60;
//         if(callback == 'rating'
//             || callback == 'discussing'
//             || callback == 'planned'
//             || callback == 'waiting_next'
//             ) {
//             intervalInSeconds = 5;
//         }
//         if(Number.isInteger(interval)){
//             intervalInSeconds = interval;
//         }
//
//         TestTake.heartBeatCallback = callback;
//         clearInterval(TestTake.heartBeatInterval);
//         TestTake.heartBeatInterval = setInterval(function() {
//             $.getJSON('/test_takes/heart_beat',
//                 function(response) {
//                     if (response.alert == 1){
//                         TestTake.alert = true;
//                     }
//                     else{
//                         if(TestTake.alert == true) {
//                             TestTake.alert = false;
//                             TestTake.startHeartBeat(callback);
//                         }
//                     }
//                     TestTake.markBackground();
//
//                     if (TestTake.heartBeatCallback == 'planned' && response.take_status == 3) {
//                         $('#btnStartTest').slideDown();
//                         $('#waiting').slideUp();
//                         clearInterval(TestTake.heartBeatInterval);
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'taken' &&
//                         response.participant_status == 3
//                     ) {
//                         Navigation.refresh();
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'taken' &&
//                         response.participant_status == 7
//                     ) {
//                         Navigation.load('/test_takes/taken_student');
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'discussing' &&
//                         response.participant_status == 7
//                     ) {
//                         Navigation.refresh();
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'active' &&
//                         response.participant_status == 5
//                     ) {
//                         TestTake.atTestStop();
//                         Navigation.refresh();
//                         Notify.notify('Toets gedwongen ingeleverd', 'error');
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'active' &&
//                         response.participant_status == 6
//                     ) {
//                         TestTake.atTestStop();
//                         Navigation.refresh();
//                         Notify.notify('Toets gedwongen ingeleverd', 'error');
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'rating' &&
//                         response.participant_status == 7 &&
//                         response.discussing_question_id != TestTake.discussingQuestionId
//                     ) {
//                         Navigation.refresh();
//                     }
//
//                     if (
//                         TestTake.heartBeatCallback == 'rating' &&
//                         response.participant_status == 8
//                     ) {
//                         Navigation.refresh();
//                     }
//                 }
//             );
//         }, intervalInSeconds*1000);
//     },
//
//     markBackground : function(){
//         console.log('mark background');
//         if(!TestTake.alert) {
//             $('#test_progress').css({
//                 'background' : '#294409'
//             });
//         }else{
//             $('#test_progress').css({
//                 'background' : 'red'
//             });
//         }
//     },
//
//     delete : function(take_id) {
//
//         Popup.message({
//             btnOk: 'Ja',
//             btnCancel: 'Annuleer',
//             title: 'Weet u het zeker?',
//             message: 'Weet u het zeker?'
//         }, function() {
//             $.get('/test_takes/delete/' + take_id,
//                 function() {
//                     Navigation.refresh();
//                 }
//             );
//         });
//     },
//
//     handIn : function() {
//         Answer.saveAnswer("void");
//
//         if($('.question.grey').length > 0) {
//             Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleren',
//                 title: 'Toets inleveren',
//                 message: 'Niet alle vragen zijn beantwoord, weet je het zeker?'
//             }, function() {
//                 TestTake.doHandIn();
//             });
//         }/*else if(!Answer.questionSaved) {
//             Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleren',
//                 title: 'Toets inleveren',
//                 message: 'Huidige vraag is nog niet opgeslagen! Weet je het zeker?'
//             }, function() {
//                 TestTake.doHandIn();
//             });
//         }*/else{
//             Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleren',
//                 title: 'Toets inleveren',
//                 message: 'Weet je zeker dat je de toets wilt inleveren?'
//             }, function() {
//                 TestTake.doHandIn();
//             });
//         }
//     },
//
//     doHandIn : function() {
//         $.get('/test_takes/hand_in',
//             function() {
//                 clearTimeout(TestTake.heartBeatInterval);
//                 stopCheckFocus();
//                 // Navigation.refresh();
//                 Navigation.load('/test_takes/taken_student');
//                 TestTake.atTestStop();
//                 Notify.notify('De toets is gestopt', 'info');
//                 TestTake.active = false;
//             }
//         );
//     },
//
//     startTest : function(take_id) {
//         TestTake.atTestStart();
//
//         setTimeout(function() {
//             // Navigation.refresh();
//             Navigation.load('/test_takes/take/' + take_id);
//         }, 500);
//     },
//
//     atTestStart : function() {
//         $.get('/test_takes/start_take_participant', function(response) {
//             if(response == 'error') {
//                 alert('Toetsafname kon niet worden gestart. Waarschuw de surveillant.');
//             }else{
//                 $('#tiles').hide();
//                 $('#header #menu').fadeOut();
//                 $('#header #logo_1').animate({
//                     'height' : '30px'
//                 });
//                 TestTake.active = true;
//                 startfullscreentimer();
//                 $('#header #logo_2').animate({
//                     'margin-left' : '50px'
//                 });
//                 $('#btnLogout').hide();
//                 $('#btnMenuHandIn').show();
//                 $('#container').animate({'margin-top' : '30px'});
//
//                 $('body').on('contextmenu',function(e){
//                     e.preventDefault();
//                     return false;
//                 });
//             }
//         });
//     },
//
//     atTestStop : function() {
//         $('#header #menu').fadeIn();
//         $('#btnLogout').show();
//         $('#btnMenuHandIn').hide();
//         TestTake.active = false;
//         stopfullscreentimer();
//         $('#header #logo_1').animate({
//             'height' : '70px'
//         });
//         $('#header #logo_2').animate({
//             'margin-left' : '90px'
//         });
//         $('#container').animate({'margin-top' : '92px'},
//             function() {
//                 $('#tiles').show();
//             }
//         );
//     },
//
//     atTestStart : function() {
//         $.get('/test_takes/start_take_participant', function(response) {
//             if(response == 'error') {
//                 alert('Toetsafname kon niet worden gestart. Waarschuw de surveillant.');
//             }else{
//                 Core.stopCheckUnreadMessagesListener();
//                 runCheckFocus();
//                 $('#tiles').hide();
//                 $('#header #menu').fadeOut();
//                 $('#header #logo_1').animate({
//                     'height' : '30px'
//                 });
//                 TestTake.active = true;
//
//                 $('#header #logo_2').animate({
//                     'margin-left' : '50px'
//                 });
//                 $('#btnLogout').hide();
//                 $('#btnMenuHandIn').show();
//                 $('#container').animate({'margin-top' : '30px'});
//
//                 $('body').on('contextmenu',function(e){
//                     e.preventDefault();
//                     return false;
//                 });
//
//                 TestTake.alert = false;
//             }
//         });
//     },
//
//     atTestStop : function() {
//         TestTake.alert = false;
//         stopCheckFocus();
//         Core.startCheckUnreadMessagesListener();
//         $('#header #menu').fadeIn();
//         $('#btnLogout').show();
//         $('#btnMenuHandIn').hide();
//         TestTake.active = false;
//
//         $('#header #logo_1').animate({
//             'height' : '70px'
//         });
//         $('#header #logo_2').animate({
//             'margin-left' : '90px'
//         });
//         $('#container').animate({'margin-top' : '92px'},
//             function() {
//                 $('#tiles').show();
//             }
//         );
//     },
//
//     saveRating : function() {
//         $.post('/test_takes/set_rating',
//             {
//                 rating : $('#answerRating').val()
//             },
//             function(response) {
//                 Navigation.refresh();
//             }
//         );
//     },
//
//     confirmEvent : function(take_id, event_id) {
//         $.get('/test_takes/confirm_event/' + take_id + '/' + event_id);
//         $('#event_' + event_id).slideUp();
//     },
//
//     addParticipantNote : function(take_id, participant_id) {
//         Popup.load('/test_takes/participant_notes/' + take_id + '/' + participant_id, 500);
//     },
//
//     saveParticipantNotes : function(take_id, participant_id) {
//         $.post('/test_takes/participant_notes/' + take_id + '/' + participant_id,
//             {
//                 'note' : $('#participant_notes').val()
//             }
//         );
//         Popup.closeLast();
//     },
//
//     selectTest : function(i) {
//         TestTake.i = i;
//
//         Popup.load('/test_takes/select_test', 1000);
//     },
//
//     selectTestTake : function() {
//         Popup.load('/test_takes/select_test_retake', 1000);
//     },
//
//     setSelectedTest : function(id, name, kind) {
//         $('#TestTakeSelect_' + TestTake.i).html(name);
//         $('#TestTakeTestId_' + TestTake.i).val(id);
//
//         if(kind == 1) {
//             $('#TestTakeWeight_' + TestTake.i).attr('disabled', true).val('0');
//         }else{
//             $('#TestTakeWeight_' + TestTake.i).attr('disabled', false);
//         }
//
//         Popup.closeLast();
//     },
//
//     setSelectedTestTake : function(id, name) {
//         $('#TestTakeSelect').html(name);
//         $('#TestTakeRetakeTestTakeId').val(id);
//         Popup.closeLast();
//     },
//
//     addTestRow : function() {
//         $('.testTakeRow:hidden').first().find('.testIsVisible:first').val(1);
//         $('.testTakeRow:hidden').first().fadeIn();
//         $('.testTakeRowNotes:hidden').first().fadeIn();
//     },
//
//     removeTestRow : function(e, i) {
//
//         $('#tableTestTakes #' + i).fadeOut().find('input').val('');
//         $('#tableTestTakes #' + i).find('.btnSelectTest').html('Selecteer');
//
//         $('#tableTestTakes #notes_' + i).fadeOut().find('input').val('');
//     },
//
//     loadParticipants : function(take_id) {
//         $.get('/test_takes/load_participants/' + take_id,
//             function(html) {
//                 $('.page[page=participants]').html(html);
//             }
//         );
//     },
//
//     removeParticipant : function(take_id, participant_id) {
//         $.ajax({
//             url: '/test_takes/remove_participant/' + take_id + '/' + participant_id,
//             type: 'DELETE',
//             success: function(response) {
//                 TestTake.loadParticipants(take_id);
//             }
//         });
//     },
//
//     closeShowResults : function(take_id) {
//         $.post('/test_takes/update_show_results/' + take_id, {
//                 active : 0,
//                 show_results : ''
//         },
//             function() {
//                 Navigation.refresh();
//             }
//         );
//     },
//
//
//     addClass : function(test_id) {
//         $.get('/test_takes/add_class/' + test_id,
//             function(response) {
//                 Navigation.refresh();
//                 Notify.notify('Klas toegevoegd', 'info');
//                 Popup.closeLast();
//             }
//         );
//     },
//
//     startTake : function(take_id) {
//         if(!TestTake.studentsPresent) {
//             Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleer',
//                 title: 'Weet u het zeker?',
//                 message: 'Niet alle Studenten zijn aanwezig.'
//             }, function() {
//                 $.get('/test_takes/start_test/' + take_id,
//                     function(response) {
//                         Notify.notify('Toetsafname gestart', 'info');
//                         Navigation.load('/test_takes/surveillance');
//                     }
//                 );
//             });
//         }else{
//             $.get('/test_takes/start_test/' + take_id,
//                 function(response) {
//                     Notify.notify('Toetsafname gestart', 'info');
//                     Navigation.load('/test_takes/surveillance');
//                 }
//             );
//         }
//     },
//
//     startRating : function(take_id, type) {
//         Navigation.load('/test_take/rate_teacher/' + take_id + '/' + type);
//     },
//
//     loadParticipantAnswerPreview : function(take_id, user_id) {
//         $('#questionAnswer').load('/test_takes/rate/' + take_id + '/' + user_id).parent().css({
//             'border-left' : '20px solid #3D9D36',
//         }).find('.block-head').css({'background-color':'#3D9D36'}).children('strong').html('Antwoord leerling');
//
//         $('#btnResetAnswerPreview').slideDown();
//         clearInterval(window.participantsTimeout);
//     },
//
//     resetAnswerPreview : function(discussing_question_id, take_id) {
//         $('#questionAnswer').load('/questions/preview_answer_load/' + discussing_question_id).parent().css({
//             'border-left' : '20px solid #197cb4'
//         }).find('.block-head').css({'background-color':'#197cb4'}).children('strong').html('Antwoordmodel');
//
//         $('#btnResetAnswerPreview').slideUp();
//         clearInterval(window.participantsTimeout);
//         window.participantsTimeout = setInterval(function() {
//             Loading.discard = true;
//             $('#participants').load('/test_takes/discussion_participants/' + take_id);
//         }, 5000);
//     },
//
//     startDiscussion : function(take_id, type) {
//         $.get('/test_takes/start_discussion/' + take_id + '/' + type,
//             function(response) {
//                 Notify.notify('Toetsbespreking gestart', 'info');
//                 Navigation.load('/test_takes/discussion/'+take_id);
//                 Popup.closeLast();
//                 User.surpressInactive = true;
//             }
//         );
//     },
//
//     nextDiscussionQuestion : function(take_id) {
//
//         if(typeof $(".nextDiscussionQuestion").attr('disabled') !== typeof undefined) return false;
//
//         $(".nextDiscussionQuestion").attr('disabled','disabled');
//
//         if(TestTake.discussingAllDiscussed) {
//             $.get('/test_takes/next_discussion_question/' + take_id,
//                 function () {
//                     Navigation.refresh();
//                     $(".nextDiscussionQuestion").removeAttr('disabled');
//                 }
//             );
//         }else{
//             Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleer',
//                 title: 'Weet u het zeker?',
//                 message: 'Niet iedereen is klaar met bespreken.'
//             }, function() {
//                 $.get('/test_takes/next_discussion_question/' + take_id,
//                     function () {
//                         Navigation.refresh();
//                         $(".nextDiscussionQuestion").removeAttr('disabled');
//                     }
//                 );
//             },
//             function() {
//                  $(".nextDiscussionQuestion").removeAttr('disabled');
//             });
//         }
//     },
//
//     checkStartDiscussion : function(take_id) {
//         if($('.participant:not(".active")').length > 0) {
//             Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleer',
//                 title: 'Weet u het zeker?',
//                 message: 'Niet alle Studenten zijn aanwezig'
//             }, function() {
//                 setTimeout(function() {
//                     Popup.load('/test_takes/start_discussion_popup/' + take_id, 420);
//                 }, 1000);
//             });
//         }else{
//             Popup.load('/test_takes/start_discussion_popup/' + take_id, 420)
//         }
//     },
//
//     finishDiscussion : function(take_id) {
//         $('.redactor-toolbar').attr('style', 'z-index: 0 !important');
//         Popup.message({
//                 btnOk: 'Ja',
//                 btnCancel: 'Annuleer',
//                 title: 'Weet u het zeker?',
//                 message: 'Weet u zeker dat u de bespreking wilt be&iuml;ndigen?'
//             }, function() {
//                 $.get('/test_takes/finish_discussion/' + take_id,
//                     function (response) {
//                         User.surpressInactive = false;
//                         Navigation.refresh();
//                         setTimeout(function() {
//                             Popup.load('/test_takes/update_show_results/' + take_id, 420);
//                         }, 1000);
//                     }
//                 );
//             }
//         );
//     },
//
//     startMultiple : function() {
//         $.each($('.test_take:checked'), function() {
//             var take_id = $(this).attr('take_id');
//             $.get('/test_takes/start_test/' + take_id);
//         });
//
//         Popup.closeLast();
//         Notify.notify('Toetsafnames gestart', 'info');
//         Navigation.load('/test_takes/surveillance');
//     },
//
//     loadClassParticipants : function(class_id) {
//         Popup.load('/test_takes/add_class_participants/' + class_id, 600);
//     },
//
//     addSelectedStudents : function(class_id) {
//         $.post('/test_takes/add_class_participants/' + class_id,
//             $('#StudentAddClassParticipantsForm').serialize(),
//             function(response) {
//                 Popup.closeLast();
//                 Notify.notify('Studenten toegevoegd', 'info');
//                 Navigation.refresh();
//             }
//         );
//     },
//
//     toggleParticipantProgress : function() {
//         if(TestTake.showProgress) {
//             TestTake.showProgress = false;
//         }else{
//             TestTake.showProgress = true;
//         }
//         Navigation.refresh();
//     },
//
//     loadTake : function(take_id, makebutton) {
//         if(Core.inApp) {
//             if(makebutton === true) {check = '/null/true'; } else  { check = '';}
//             Navigation.load('/test_takes/take/' + take_id + check);
//         }else{
//             Notify.notify("niet in beveiligde omgeving <br> download de laatste app versie via <a href=\"http://www.test-correct.nl\">http://www.test-correct.nl</a>", "error");
//         }
//     },
//
//     loadTakeInLaravel : function(take_id, makebutton) {
//         // if(Core.inApp) {
//             if(makebutton === true) {check = '/null/true'; } else  { check = '';}
//             $.ajax({
//                type:'post',
//                url: '/test_takes/startinlaravel/'+take_id + check,
//                dataType: 'json',
//                data: {},
//                success: function(data){
//                    window.open(data.data.url, '_self');
//                },
//             });
//         // }else{
//         //     Notify.notify("niet in beveiligde omgeving <br> download de laatste app versie via <a href=\"http://www.test-correct.nl\">http://www.test-correct.nl</a>", "error");
//         // }
//     },
//
//     loadDiscussion : function(take_id) {
//         // @@ OFFLINE ivm Corona
//         // if(Core.inApp) {
//             Navigation.load('/test_takes/discuss/' + take_id);
//         // }else{
//         //     Notify.notify("niet in beveiligde omgeving <br> download de laatste app versie via <a href=\"http://www.test-correct.nl\">http://www.test-correct.nl</a>", "error");
//         // }
//     },
//
//     ipAlert : function() {
//         Popup.message({
//             btnOk : 'Oke',
//             title : 'Incorrect IP-adres',
//             message : 'Deze Student bevindt zich op een incorrect ip-adres'
//         });
//     },
//
//     updatePeriodOnDate : function(e, i) {
//         var date = $(e).val();
//
//         $.post('/test_takes/get_date_period',
//             {
//                 date : date
//             },
//             function(response) {
//                 // console.log(response);
//                 // console.log(i);
//                 if(response != "") {
//                     $('#TestTakePeriodId_' + i).val(response);
//                 }
//             }
//         );
//     },
//
//     forceTakenAway : function(take_id, participant_id) {
//
//         Popup.message({
//             btnOk: 'Ja',
//             btnCancel: 'Annuleer',
//             title: 'Weet u het zeker?',
//             message: 'Weet u zeker dat u de toets wil innemen?'
//         }, function() {
//             $.get('/test_takes/force_taken_away/' + take_id + '/' + participant_id,
//                 function () {
//                     Navigation.refresh();
//                 }
//             );
//         });
//     },
//
//     forcePlanned : function(take_id, participant_id) {
//         $.get('/test_takes/force_planned/' + take_id + '/' + participant_id,
//             function() {
//                 Navigation.refresh();
//             }
//         );
//     },
//
//     setTakeTaken : function(take_id) {
//
//         Popup.message({
//             btnOk: 'Ja',
//             btnCancel: 'Annuleer',
//             title: 'Weet u het zeker?',
//             message: 'Weet je zeker dat je de toets wilt innemen?'
//         }, function() {
//             $.get('/test_takes/set_taken/' + take_id,
//                 function() {
//                     Navigation.refresh();
//                 }
//             );
//         });
//
//     },
//
//     setFinalRate : function(take_id, participant_id, rate) {
//         $.get('/test_takes/set_final_rate/' + take_id + '/' + participant_id + '/' + rate,
//             function() {
//                 Notify.notify('Score opgeslagen');
//             }
//         );
//     },
//
//     markRated : function(take_id) {
//         $.get('/test_takes/mark_rated/' + take_id,
//             function() {
//                 Notify.notify("Als becijferd gemarkeerd");
//                 Navigation.load('/test_takes/view/' + take_id);
//             }
//         );
//     },
//
//     saveTeacherRating : function(answer_id, score, participant_id, rating_id, question_id) {
//         $.post('/test_takes/rate_teacher_score',
//             {
//                 'answer_id' : answer_id,
//                 'score' : score,
//                 'new' : 1,
//                 'rating_id' : rating_id
//             },
//             function(response) {
//                 $('#score_' + participant_id + question_id).load('/test_takes/rate_teacher_score/' + participant_id + '/' + question_id);
//             }
//         );
//     },
//
//     saveNormalization : function(take_id) {
//         $.post('/test_takes/normalization/' + take_id,
//             $('#TestTakeNormalizationForm').serialize(),
//             function(response) {
//                 Notify.notify('Normering toegepast', 'info');
//                 Navigation.load("/test_takes/set_final_rates/" + take_id);
//             }
//         );
//     },
//
//     normalizationPreview : function(take_id) {
//         $.post('/test_takes/normalization_preview/' + take_id,
//             $('#TestTakeNormalizationForm').serialize(),
//             function(response) {
//                 $('#divPreview').html(response);
//             }
//         );
//     },
//
//     loadParticipantResults : function(participant_id) {
//
//     },
//
//     getTestTakeAttainmentAnalysisDetails : function(take_id,attainment_id,callback) {
//         $.get('/test_takes/attainment_analysis_per_attainment/' + take_id+'/'+attainment_id,
//             function(response) {
//                 callback(response);
//             }
//         );
//     },
//     archive : function(e, take_id) {
//
//         $.get('/test_takes/archive/'+take_id, function(response) {
//             Notify.notify('De toets is gearchiveerd, je kunt het archiveringsfilter gebruiken om de toets te dearchiveren.');
//         });
//         var row = $(e).parents('tr:first');
//         $(e).parents('tr:first').addClass('jquery-has-just-been-archived').addClass('jquery-archived').removeClass('jquery-not-archived');
//         if(row.hasClass('jquery-hide-when-archived')){
//             row.find('td').fadeOut(1600);
//         }
//     },
//     unarchive : function(e,take_id) {
//         $.get('/test_takes/unarchive/'+take_id, function(response) {
//             Notify.notify('De toets is gedearchiveerd.');
//             $(e).parents('tr:first').addClass('jquery-not-archived').removeClass('jquery-archived');
//         });
//     },
//     loadDetails:function (e, take_id) {
//         if ($(e).parents('tr:first').hasClass('jquery-archived')) {
//             Notify.notify('Dearchiveer deze toets om de details in te zien.');
//             return;
//         }
//         Navigation.load('/test_takes/view/'+take_id);
//     },
// };
//
//
// var hidden = "hidden";
//
// // Standards:
// if (hidden in document){
//     document.addEventListener("visibilitychange", onchange);
// } else if ((hidden = "mozHidden") in document){
//     document.addEventListener("mozvisibilitychange", onchange);
// } else if ((hidden = "webkitHidden") in document){
//     document.addEventListener("webkitvisibilitychange", onchange);
// } else if ((hidden = "msHidden") in document){
//     document.addEventListener("msvisibilitychange", onchange);
// }
// // IE 9 and lower:
// else if ("onfocusin" in document){
//     document.onfocusin = document.onfocusout = onchange;
// }
// // All others:
// else{
//     window.onpageshow = window.onpagehide = window.onfocus = window.onblur = onchange;
// }
//
// function onchange (evt) {
//     var v = "visible", h = "hidden",
//         evtMap = {
//           focus:v, focusin:v, pageshow:v, blur:h, focusout:h, pagehide:h
//         };
//
//     evt = evt || window.event;
//
//     if (evt.type in evtMap){
//       document.body.className = evtMap[evt.type];
//     } else{
//       document.body.className = this[hidden] ? "hidden" : "visible";
//     }
//     if(this[hidden] && typeof Core !== "undefined"){
//         console.log('lostfocus');
//         Core.lostFocus();
//     }
// }
//
// var checkFocusTimer = false;
// function runCheckFocus(){
//     if(!checkFocusTimer) {
//         checkFocusTimer = setInterval(checkPageFocus, 300);
//     }
// }
//
// function stopCheckFocus(){
//     if(checkFocusTimer){
//         clearInterval(checkFocusTimer);
//         checkFocusTimer = false;
//     }
// }
//
// var fullscreentimer;
// function checkfullscreen(){
//     if(window.innerWidth !== screen.width || window.innerHeight !== screen.height) {
//         console.log('hand in from checkfullscreen');
//         Core.lostFocus();
//     }
// }
// function startfullscreentimer(){
//     if(window.navigator.userAgent.indexOf('CrOS') > 0) {
//         fullscreentimer = setInterval(checkfullscreen, 300);
//     }
// }
//
// function stopfullscreentimer(){
//     clearInterval(fullscreentimer);
// }
//
// parent.skip = false;
// var notifsent= false;
//
// function closebtu(){
//     //we call this func. from drawing_answer_canvas.ctp when someone press close button
//     parent.skip = true;
//     window.parent.Popup.closeLast();
// }
//
// function checkPageFocus(){
//     if(!parent.skip){
//         if (!document.hasFocus()) {
//             if(!notifsent){  // checks for the notifcation if it is already sent to the teacher
//                 console.log('lost focus from checkPageFocus');
//                 Core.lostFocus();
//                 notifsent= true;
//             }
//         } else {
//             notifsent= false;  //mark it as not sent, to active it again
//         }
//     } else {
//         window.focus();   //we need to set focus back to the window before changing skip value
//         parent.skip = false;
//     }
// }
//
//
//
// // set the initial state (but only if browser supports the Page Visibility API)
// // $(document).ready(function(){
// //     if( document[hidden] !== undefined ) {
// //         onchange({type: document[hidden] ? "blur" : "focus"});
// //     }
// // });
