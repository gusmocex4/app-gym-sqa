
		<nav class="nav-main" id="nav-main">
			<div class="contenedor nav-menu">
				<img src="build/img/logo.png" class="nav-brand" />
				<div class="nav-ref">
                    <a href="#inicio">Inicio</a>
					<a href="#sobreNosotros">Sobre nosotros</a>
					<a href="#servicios">Servicios</a>
					<a href="#contacto">Contacto</a>
				</div>
				<div class="inicioSesion">
					<a href="/login">Iniciar sesión</a>
				</div>
			</div>
		</nav>

		<div class="video" id="inicio">
			<div class="overlay">
				<div class="contenedor texto-video">
					<h2>Fuerza, Fitness y Bienestar.</h2>
					<p>La transformación es un proceso, y cada entrenamiento es una invitación a desafiar lo que creías posible. En este gimnasio, no solo forjamos músculos, forjamos carácter y deerminación.</p>
				</div>
			</div>
			<video autoplay muted loop>
				<source src="build/vid/videoFlexiones.mp4" type="video/mp4" />
				<source src="build/vid/videoFlexiones.webm" type="video/webm" />
			</video>
		</div>

		<div class="contenedor sobreNosotros" id="sobreNosotros">
			<div class="sn-texto">
				<h2>Sobre nosotros</h2>
				<p>Nuestra misión es simple: inspirarte a vivir una vida activa y saludable. Creemos que el ejercicio regular, la nutrición adecuada y el apoyo mutuo son fundamentales para lograr un estilo de vida equilibrado. En FitZone, te ofrecemos las herramientas y el apoyo que necesitas para alcanzar tus metas, ya sea que estés buscando perder peso, ganar fuerza o simplemente mantenerte en forma.</p>
			</div>
			<div class="sn-img">
				<img src="build/img/img1.png" />
			</div>
		</div>

		<div class="contenedor servicios" id="servicios">
			<div class="s-texto">
				<div class="entrenamiento">
					<span class="material-symbols-outlined">
						fitness_center
					</span>
					<h4>Entrenamiento personalizado</h4>
					<p>Programas adaptados a las necesidades y capacidades individuales.</p>
				</div>
				<div class="nutricion">
					<span class="material-symbols-outlined">
						nutrition
					</span>
					<h4>Nutricion y bienestar</h4>
					<p>Asesoramiento nutricional y planes de dieta.</p>
				</div>
				<div class="clases">
					<span class="material-symbols-outlined">
						groups
					</span>
					<h4>Clases de grupo</h4>
					<p>Yoga, pilates, spinning, zumba, body pump, entre otras.</p>
				</div>
				<div class="cardio">
					<span class="material-symbols-outlined">
						directions_bike
					</span>
					<h4>Área de cardio</h4>
					<p>Cintas de correr, bicicletas estáticas, elípticas y otros equipos.</p>
				</div>
			</div>
		</div>
		
		<div class="contenedor contacto" id="contacto">

			<div class="mapa">
				<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d125550.80693591612!2d-67.12063575664058!3d10.464141900000017!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c2a5ff4fca985bf%3A0x43cb2d8100a63708!2sUniversidad%20Cat%C3%B3lica%20Andr%C3%A9s%20Bello!5e0!3m2!1ses-419!2sve!4v1715230904813!5m2!1ses-419!2sve" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>			

			<div class="cont-form">
				<p>¿Tienes dudas? Contactate con nosotros</p> 
				<div class="form">
					<form id="contact_form" action="#" method="POST" enctype="multipart/form-data">
						<div class="dato">
							<input id="name" class="input" name="name" type="text" placeholder="Tu nombre" />
							<span id="name_validation" class="error_message"></span>
						</div>
						<div class="dato">
							<input id="email" class="input" name="email" type="text" placeholder="Tu email" />
							<span id="email_validation" class="error_message"></span>
						</div>
						<div class="dato">
							<textarea id="message" class="input" name="message" rows="7" cols="30" placeholder="Tu mensaje"></textarea>
							<span id="message_validation" class="error_message"></span>
						</div>
						<input id="submit_button" type="submit" value="Send email" />
					</form>
				</div>
			</div>
			
		</div>

		<footer class="foot">
			<p>FitZone. Todos los derechos reservados.</p>
		</footer>
