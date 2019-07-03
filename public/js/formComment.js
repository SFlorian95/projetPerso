// cibler le formulaire de commentaire

let formComment = $('.form-comment');
console.log(formComment);

// écouteur événement submit 
formComment.on('submit', submitFormComment);

//gestionnaire de l'événement submit
function submitFormComment(e){
    e.preventDefault();
    
    let formData = new FormData(e.target);
    
         $.ajax({
         method:'post',
         dataType: 'json',
         data:formData,
         url:'/user/comment/add/',
         success: commentAddSuccess,
         processData:false,
         contentType:false
     });
    //console.log(data.entries().next());
    
     //fonction appelée apres la reponse http
     //le paramètre permet de récupérer les données de la réponse http
     function commentAddSuccess(response){
        /*
         * empty : vider la liste des commentaires (le contenu d'une balise)
         */
         $('.comment-list').empty();
         
        //réinitialiser le formulaire
         formComment[0].reset();
         
        // boucle sur la réponse http
         response.forEach( comment => {
             // formater la date
             let date = new Date( comment.datetime.date );
             console.log(date);
             // append: ajouter du html en fin de balise sans supprimer le contenu précédent
             $('.comment-list').append(` 
                            <hr>
                            <p>${comment.content}</p>
             <time class="font-italic text-black-50">
                                Posté le ${date.toLocaleString()}
                                à ${date.toLocaleTimeString()}
                            </time>
                            `);
         } );
         
         console.log(response);
         
     }
}


