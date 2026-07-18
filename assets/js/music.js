const grid = document.getElementById(
"song-explorer-grid"
);

const search = document.getElementById(
"song-search"
);


function displaySongs(data){

grid.innerHTML="";


data.forEach((song,index)=>{


grid.innerHTML += `


<article class="song-card">


<div class="song-meta">

<span class="song-year">
${song.year}
</span>


</div>



<h3>
${song.title}
</h3>



<p class="song-album">
${song.album}
</p>



<p class="song-writer">

Written by ${song.writer}

</p>



<p class="song-preview">

${song.story.substring(0,130)}...

</p>




<button 
class="story-btn"
onclick="openStory(${index})">

Read Story →

</button>



</article>


`;


});


}

function openStory(index){


const song = queenSongs[index];


const modal=document.createElement("div");


modal.className="queen-story-modal";



modal.innerHTML=`


<div class="queen-story-box">


<button class="close-story">
×
</button>



<span class="story-year">
${song.year}
</span>



<h2>
${song.title}
</h2>



<p class="story-album">
${song.album}
</p>



<p class="story-writer">
Written by ${song.writer}
</p>



<div class="story-divider"></div>




<h3>
Behind The Song
</h3>



<p class="story-description">

${song.story}

</p>





<div class="story-highlight">


<h4>
Why This Song Matters
</h4>


<ul>

${song.importance.map(item=>`

<li>
${item}
</li>

`).join("")}

</ul>

<ul>

<li>
Shows Queen's creativity and musical identity
</li>

<li>
Still connects with listeners worldwide
</li>


</ul>


</div>


<div class="story-player">

<h4>
Listen To This Song
</h4>


<iframe

style="border-radius:12px"

src="${song.spotify}"

width="100%"

height="152"

frameborder="0"

allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"

loading="lazy">

</iframe>


</div>



</div>


`;



document.body.appendChild(modal);




modal.querySelector(".close-story")
.onclick=()=>{

modal.remove();

};




modal.onclick=(e)=>{


if(e.target===modal){

modal.remove();

}

};



}


displaySongs(queenSongs);


search.addEventListener(
"input",
()=>{


const value =
search.value.toLowerCase().trim();



const result =
queenSongs.filter(song=>{


return (

song.title
.toLowerCase()
.includes(value)



||

song.album
.toLowerCase()
.includes(value)



||

song.writer
.toLowerCase()
.includes(value)



||

song.year
.toLowerCase()
.includes(value)



||

song.story
.toLowerCase()
.includes(value)


);


});



displaySongs(result);



});

window.openStory = openStory;
