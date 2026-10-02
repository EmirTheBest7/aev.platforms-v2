const facts = [
	{ 
    "title" : "The trembita",
		"fact" : "The world’s longest musical instrument also originates from Ukraine. The pipe piece is aptly named Trembita."
	},
  
  { 
    "title" : "The Easter egg",
		"fact" : "The popular Easter egg tradition originated in Ukraine. Originally, the eggs were patterned using wax and dye. The wax was eventually removed leaving an impressive pattern with dashing colors."
	},
  
  { 
    "title" : "Their wedding ring",
		"fact" : "Unlike many cultures around the world, Ukrainians wear their wedding rings on the right hand, not on the left."
	},
  
  { 
    "title" : "Protection of Jews",
		"fact" : "Thousands of Jews were offered protection by Ukrainian religious leaders during the World War II. One of the leaders, Metropolitan Andrey Sheptytsky was honored in 2013 by the Anti-Defamation League for his humanitarian role. The Jews found refuge in monasteries and Ukrainian homes."
	},
  
  { 
    "title" : "Hero City",
		"fact" : "On 22 June 1941, German armies invaded the Soviet Union, initiating nearly four years of total war. The Axis initially advanced against desperate but unsuccessful efforts of the Red Army. In the encirclement battle of Kyiv, the city was acclaimed as a 'Hero City', because of its fierce resistance." 
	},
  
  { 
    "title" : "Leading production of sunflower oil",
		"fact" : "Ukraine leads in the world in the production of sunflower oil. It is also among the largest producers of corn, wheat, potato, sugar beet, barley, tomatoes, apples, pumpkins, carrots, cucumbers, cabbage, rye, walnuts, buckwheat, dry peas, and honey."
	},
  
  { 
    "title" : "The first gas lamp",
		"fact" : "The invention of the first gas lamp took place in Lviv. It was invented by a local pharmacist in a store called At the Golden Star."
	},
  
	{ 
    "title" : "The President",
		"fact" : "Volodymyr Oleksandrovych Zelenskyy is a Ukrainian politician, former actor and comedian, who is the sixth and current president of Ukraine."
	},
  {
    "title": "O Sole Mio",
    "fact": "The world's famous song 'O Sole Mio' was composed in Odesa, Ukraine."
  }
]

function randomFact() {
  let random = facts[Math.floor(Math.random() * facts.length)];
  fact.innerText = `${random.fact}`;
  title.innerText = random.title;
}

randomFact();

document.querySelector("button").addEventListener('click', randomFact)