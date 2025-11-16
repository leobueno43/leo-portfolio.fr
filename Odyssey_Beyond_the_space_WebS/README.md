🌠 Site web : Voyage dans l’Espace (Odyssey Behond the Space)

Un site éducatif et immersif sur l’univers, qui fait découvrir les planètes, les étoiles et les grandes missions spatiales à travers 5 pages liées entre elles, avec images, sons, vidéo et boutons interactifs.

🌍 Page 1 — Accueil (index.html)
🎯 Objectif :

Accueillir le visiteur et le plonger immédiatement dans une ambiance spatiale.

🧩 Contenu :

Titre principal : “Voyage dans l’Espace”

Sous-titre : “Découvrez les merveilles de l’univers.”

Texte introductif :

“Bienvenue à bord, explorateur ! Ce site vous emmènera à travers les planètes, les étoiles et les plus grandes missions spatiales de l’humanité.”

Bouton principal : “Commencer le voyage” → mène vers planetes.html

Menu de navigation :
Accueil | Planètes | Étoiles | Exploration | Contact

🎵 Son :

ambiance.mp3 — musique spatiale douce (jouée en boucle à bas volume).

🖼️ Images :

fond-accueil.jpg : une galaxie ou un ciel étoilé (fond plein écran).

logo-fusee.png : petite icône de fusée à côté du titre.

💅 Style CSS :

Fond sombre (#0a0a1a) avec étoiles scintillantes.

Titre en police “Orbitron”, texte centré verticalement.

Bouton lumineux bleu avec effet hover (brillance).

🪐 Page 2 — Les Planètes (planetes.html)
🎯 Objectif :

Faire découvrir les planètes du système solaire avec images et descriptions.

🧩 Contenu :

Titre principal : “Les Planètes du Système Solaire”

Texte d’introduction :

“Notre système solaire est composé de huit planètes gravitant autour du Soleil. Chacune est unique, de la brûlante Mercure à la glaciale Neptune.”

Cartes des planètes :
Chaque planète aura une image, un texte et un bouton “Découvrir”.

Planète	Image	Description courte
🌞 Soleil	soleil.jpg	L’étoile au centre de notre système, source de toute lumière.
🪨 Mercure	mercure.jpg	La planète la plus proche du Soleil, petite et brûlante.
🌫️ Vénus	venus.jpg	Enveloppée de nuages acides, c’est la plus chaude.
🌍 Terre	terre.jpg	Notre planète bleue, pleine de vie et d’eau.
🔴 Mars	mars.jpg	La planète rouge, cible de futures missions humaines.
🌀 Jupiter	jupiter.jpg	Géante gazeuse avec sa Grande Tache Rouge.
💍 Saturne	saturne.jpg	Célèbre pour ses majestueux anneaux.
🧊 Uranus	uranus.jpg	Une planète bleue-verte inclinée sur le côté.
🔵 Neptune	neptune.jpg	La plus éloignée, fouettée par des vents violents.
🖼️ Images :

9 images (une par planète + Soleil) :

/media/soleil.jpg
/media/mercure.jpg
/media/venus.jpg
/media/terre.jpg
/media/mars.jpg
/media/jupiter.jpg
/media/saturne.jpg
/media/uranus.jpg
/media/neptune.jpg

💅 Style CSS :

display: grid; pour aligner les planètes.

Chaque carte : fond légèrement transparent, texte blanc, bord arrondi.

Bouton “Découvrir” avec effet hover (changement de couleur).

🌌 Page 3 — Les Étoiles et les Galaxies (etoiles.html)
🎯 Objectif :

Montrer la beauté des étoiles et galaxies à travers images et vidéo.

🧩 Contenu :

Titre principal : “Les Étoiles et les Galaxies”

Texte introductif :

“Les étoiles illuminent notre ciel nocturne, et les galaxies regroupent des milliards d’entre elles. Ensemble, elles forment la trame lumineuse de l’univers.”

Section images :
Une galerie d’images avec descriptions :

Image	Fichier	Description
Voie Lactée	voie-lactee.jpg	Notre galaxie, immense spirale d’étoiles.
Nébuleuse d’Orion	nebuleuse-orion.jpg	Un berceau de nouvelles étoiles.
Andromède	andromede.jpg	La galaxie la plus proche de la nôtre.
Supernova	supernova.jpg	Explosion spectaculaire d’une étoile mourante.

Vidéo intégrée :

<iframe width="560" height="315" src="https://www.youtube.com/embed/H7vy9BhXx_M" title="Cycle de vie des étoiles"></iframe>

💅 Style CSS :

Galerie en flex-wrap, images arrondies avec ombre.

Animation légère d’étoiles en fond.

Vidéo centrée avec cadre brillant.

🚀 Page 4 — L’Exploration Spatiale (exploration.html)
🎯 Objectif :

Faire découvrir les grandes missions humaines dans l’espace.

🧩 Contenu :

Titre principal : “L’Exploration Spatiale”

Texte :

“Depuis des décennies, l’humanité explore le cosmos. Des premiers pas sur la Lune aux missions sur Mars, chaque voyage repousse les limites de notre savoir.”

Sections :

Mission	Image	Description	Bouton
Apollo 11	apollo.jpg	Premier alunissage en 1969 par Neil Armstrong.	[Découvrir]
ISS	iss.jpg	Station spatiale internationale en orbite depuis 1998.	[Découvrir]
Mars Rover	marsrover.jpg	Robots explorant la planète rouge.	[Découvrir]
James Webb	jwst.jpg	Télescope spatial observant les confins de l’univers.	[Découvrir]

Bouton spécial : “Décollage !” → déclenche le son fusee.mp3

🖼️ Images :
/media/apollo.jpg
/media/iss.jpg
/media/marsrover.jpg
/media/jwst.jpg
/media/fusee.jpg (optionnelle)

💅 Style CSS :

Disposition en cartes horizontales.

Effet hover de zoom sur chaque image.

Boutons avec effet de lueur orange/rouge.

📬 Page 5 — Contact / Rejoindre la mission (contact.html)
🎯 Objectif :

Permettre à l’utilisateur d’envoyer un message ou un avis.

🧩 Contenu :

Titre principal : “Rejoignez la mission !”

Texte :

“Envoyez-nous un message pour partager vos découvertes ou rejoindre notre équipage interstellaire.”

Formulaire :

<form>
  <label>Nom :</label>
  <input type="text" required><br>
  <label>Email :</label>
  <input type="email" required><br>
  <label>Message :</label>
  <textarea required></textarea><br>
  <button id="envoyer">Envoyer</button>
</form>


Son au clic sur le bouton “Envoyer” :

clic.mp3 → petit son futuriste.

🖼️ Images :
/media/contact-bg.jpg (fond d’espace flou)

💅 Style CSS :

Formulaire centré dans un cadre semi-transparent.

Bouton avec effet de pulsation lumineuse.

Police futuriste, texte blanc.

🗂️ Résumé général des médias
Type	Nom de fichier	Utilisation
Image	fond-accueil.jpg	Fond de la page d’accueil
Image	logo-fusee.png	Logo du site
Audio	ambiance.mp3	Musique d’ambiance sur l’accueil
Image	soleil.jpg à neptune.jpg	Les 9 planètes
Image	voie-lactee.jpg, nebuleuse-orion.jpg, andromede.jpg, supernova.jpg	Page étoiles
Vidéo	(intégration YouTube)	“Cycle de vie des étoiles”
Image	apollo.jpg, iss.jpg, marsrover.jpg, jwst.jpg, fusee.jpg	Exploration spatiale
Audio	fusee.mp3	Son de lancement
Image	contact-bg.jpg	Fond de la page contact
Audio	clic.mp3	Son de bouton “Envoyer”

➡️ Total : 20 images, 3 sons, 1 vidéo