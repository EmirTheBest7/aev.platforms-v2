// if the pen is in thumbnail view, scale it up
if (location.pathname.match(/fullcpgrid/i) ? true : false) {
    document.documentElement.style.fontSize = "32px"
    document.querySelector(".aev-notifications").style.transform = "translate(0.5rem, calc(-50% + 3rem))"
  }
  
  
  class Notifications {
    constructor(el) {
      this.el = el
    }
    
    // function to create new elements with a class (cleans up code)
    createDiv(className = "") {
      const el = document.createElement("div")
      el.classList.add(className)
      return el
    }
    // function to add text nodes to elements
    addText(el, text) {
      el.appendChild(document.createTextNode(text))
    }
    
    create(
      title = "Untitled notification",
      description = "",
      duration = 2,
      destroyOnClick = true,
      clickFunction = undefined
    ) {
      
      // functions
      function destroy(animate) {
        if (animate) {
          notiEl.classList.add("out")
          notiEl.addEventListener("animationend", () => {notiEl.remove()})
        } else {
          notiEl.remove()
        }
      }
      
      // create the elements and add their content
      const notiEl = this.createDiv("noti")
      const notiCardEl = this.createDiv("noticard")
      const glowEl = this.createDiv("notiglow")
      const borderEl = this.createDiv("notiborderglow")
      
      const titleEl = this.createDiv("notititle")
      this.addText(titleEl, title)
      
      const descriptionEl = this.createDiv("notidesc")
      this.addText(descriptionEl, description)
      
      // append the elements to each other
      notiEl.appendChild(notiCardEl)
      notiCardEl.appendChild(glowEl)
      notiCardEl.appendChild(borderEl)
      notiCardEl.appendChild(titleEl)
      notiCardEl.appendChild(descriptionEl)
      
      this.el.appendChild(notiEl)
      
      // transition the height of the container to the height of the visible card
      requestAnimationFrame(function() {
        notiEl.style.height = "calc(0.25rem + " + notiCardEl.getBoundingClientRect().height + "px)";
      });
  
      // hover animation
      notiEl.addEventListener("mousemove", (event) => {
        const rect = notiCardEl.getBoundingClientRect()
        const localX = (event.clientX - rect.left) / rect.width
        const localY = (event.clientY - rect.top) / rect.height
        glowEl.style.left = localX * 100 + "%"
        glowEl.style.top = localY * 100 + "%"
  
        borderEl.style.left = localX * 100 + "%"
        borderEl.style.top = localY * 100 + "%"
      });
      
      // onclick function if one is set
      if (clickFunction != undefined) {
        notiEl.addEventListener("click", clickFunction)
      }
      
      // destroy the notification on click if it is set to do so
      if (destroyOnClick) {
        notiEl.addEventListener("click", () => destroy(true))
      }
      
      
      // remove the notification after the set time if there is one
      if (duration != 0) {
        setTimeout(() => {
          notiEl.classList.add("out")
          notiEl.addEventListener("animationend", () => {notiEl.remove()})
        }, duration * 1000)
      }
      return notiEl
    }
  }
  
  // demo
  window.notis = new Notifications(document.querySelector(".aev-notifications"))
  
  const demonotis = [
    //() => {notis.create("Click me", "try clicking this for a surprise!", 5, true, () => notis.create("Surprise", "Wow, you clicked the previous notification,", 4))},
    () => {notis.create("Welcome to ΛΞV 🚀🌟", "Hi! Thanks for stopping by. At ΛΞV, we’re passionate about technology. Let’s embark on this digital journey together! 💡", 10)},
    () => {notis.create("Collaboration", "We believe in the power of collaboration. Connect with fellow enthusiasts, share ideas, and let’s build something amazing together", 10)},
    () => {notis.create("Inovations", "Buckle up! Our tech journey is full of twists, turns, and magic. Let’s innovate together! 🌐✨", 10)},
    () => {notis.create("Explore", "Explore, ask, and geek out! Let’s learn and grow together. 🤓👩‍💻👨‍💻", 10)},
  ]
  let i = 1;
  if (document.querySelector(".aev-notifications")) demonotis[0]()
  setInterval(()=>{
    if (i == demonotis.length) {
      notis.create("Thank you!", "Stay tuned for more updates and exciting features in our community.", 0, true, () => {i = 0})
    } else if (i < demonotis.length) {
      demonotis[i]()
    }
    i++
  }, 4000)
  
  /*
  How to use:
  
  create the notification object using new Notifications and pass it the notification wrapper element
  ex: const window.notis = new Notifications(document.querySelector(".aev-notifications"))
  
  make notifications by using notis.create() (or *.create() if you names it something else)
  
  notis.create() parameters:
    Title: string,
    Description: string,
    Duration: seconds (default 2s, 0 makes it stay forever),
    Destroy on click: boolean
      (determines if the notification should disappear when clicked, default: false)
    Click function: function
      (gets called when the notification is clicked if it isnt undefined, default: undefined)
  */