const historyItems = document.querySelectorAll(
    ".timeline-item"
);


const historyObserver = new IntersectionObserver(
    (entries)=>{

        entries.forEach(entry=>{

            if(entry.isIntersecting){

                entry.target.classList.add("show");

                historyObserver.unobserve(entry.target);

            }

        });

    },
    {
        threshold:0.35
    }
);



historyItems.forEach(item=>{

    historyObserver.observe(item);

});