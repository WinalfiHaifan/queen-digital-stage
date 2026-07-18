const albumContainer = document.getElementById(
    "album-carousel"
);


function loadAlbums(){


    albumContainer.innerHTML="";


    queenAlbums.forEach(album=>{


        albumContainer.innerHTML += `


        <article class="album-card">


            <img 
            src="${album.image}"
            class="album-cover"
            alt="${album.title}"
            >


            <span class="album-year">
            ${album.year}
            </span>


            <h3>
            ${album.title}
            </h3>


            <p>
            ${album.description}
            </p>



            <iframe
            class="album-player"
            src="${album.spotify}"
            loading="lazy"
            frameborder="0"
            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture">
            </iframe>


        </article>


        `;


    });


}


loadAlbums();





// BUTTON ARROW

const nextButton =
document.getElementById("album-next");


const prevButton =
document.getElementById("album-prev");



nextButton.addEventListener(
"click",
()=>{

albumContainer.scrollBy({

left:380,

behavior:"smooth"

});

});



prevButton.addEventListener(
"click",
()=>{

albumContainer.scrollBy({

left:-380,

behavior:"smooth"

});

});




// DRAG SCROLL

let isDown=false;

let startX;

let scrollLeft;



albumContainer.addEventListener(
"mousedown",
(e)=>{


isDown=true;

albumContainer.style.cursor="grabbing";


startX =
e.pageX - albumContainer.offsetLeft;


scrollLeft =
albumContainer.scrollLeft;


});




albumContainer.addEventListener(
"mouseleave",
()=>{

isDown=false;

albumContainer.style.cursor="grab";

});



albumContainer.addEventListener(
"mouseup",
()=>{

isDown=false;

albumContainer.style.cursor="grab";

});




albumContainer.addEventListener(
"mousemove",
(e)=>{


if(!isDown)
return;



e.preventDefault();



const x =
e.pageX - albumContainer.offsetLeft;



const walk =
(x-startX)*1.5;



albumContainer.scrollLeft =
scrollLeft-walk;


});