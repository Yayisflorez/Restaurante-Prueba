<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabor & Tradición</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <!-- Estilos -->
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>

<body>

    <!-- Encabezado / Navegación -->
    <header>
        <a href="#inicio" class="logo">
            <img src="{{ asset('img/LogoRestaurant.png') }}" alt="Logo Sabor & Tradición" class="logo-img">
        </a>
        <nav>
            <ul>
                <li><a href="#destacados">Destacados</a></li>
                <li><a href="#menu">Menú</a></li>
                <li><a href="#testimonios">Testimonios</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
        </nav>
        <div class="nav-buttons">
            <a href="{{ route('login') }}" class="btn btn-outline">Iniciar Sesión</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Registrarse</a>
        </div>
    </header>

    <!-- Banner Grande -->
    <section id="inicio" class="hero">
        <div class="hero-content">
            <h1>Sabor & Tradición</h1>
            <p>Donde la alta cocina abraza las recetas de antaño</p>
            <a href="#menu" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Ver Menú</a>
        </div>
    </section>

    <!-- Comidas Destacadas -->
    <section id="destacados">
        <h2 class="section-title">Comidas Destacadas</h2>
        <div class="featured-grid">
            <div class="featured-card">
                <img src="https://images.unsplash.com/photo-1544025162-811114215b3e?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
                    alt="Plato Destacado 1">
                <div class="featured-info">
                    <h3>Lomo Saltado Premium</h3>
                    <p>La joya de la casa. Lomo fino salteado al wok con una reducción especial.</p>
                </div>
            </div>
            <div class="featured-card">
                <img src="https://images.unsplash.com/photo-1563379926898-05f4575a45d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
                    alt="Plato Destacado 2">
                <div class="featured-info">
                    <h3>Pasta Trufada</h3>
                    <p>Fettuccine artesanal en salsa cremosa de trufas negras y parmesano.</p>
                </div>
            </div>
            <div class="featured-card">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80"
                    alt="Plato Destacado 3">
                <div class="featured-info">
                    <h3>Hamburguesa Sabor & Tradición</h3>
                    <p>Doble carne Angus, queso cheddar añejado, tocino crujiente y salsa secreta.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Menú -->
    <section id="menu" style="background-color: #1a1a1a;">
        <h2 class="section-title">Nuestro Menú</h2>

        <div class="menu-categories">
            @forelse ($categorias as $index => $categoria)
                <button class="menu-category-btn {{ $index == 0 ? 'active' : '' }}">{{ $categoria->nombre }}</button>
            @empty
                <p style="color: #aaa; text-align: center; width: 100%;">No hay categorías disponibles.</p>
            @endforelse
        </div>

        <div class="menu-grid">
            @foreach ($categorias as $categoria)
                @foreach ($categoria->platos as $plato)
                    <div class="menu-item">
                        <img src="{{ $plato->imagen ?? 'https://images.unsplash.com/photo-1544025162-811114215b3e?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80' }}"
                            alt="{{ $plato->nombre }}">
                        <div class="menu-item-details">
                            <div class="menu-item-header">
                                <h3>{{ $plato->nombre }}</h3>
                                <span class="menu-item-price">${{ number_format($plato->precio, 2) }}</span>
                            </div>
                            <p class="menu-item-desc">{{ $plato->descripcion }}</p>
                            <button class="btn btn-primary btn-order">Pedir</button>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>

    <!-- Testimonios -->
    <section id="testimonios" class="testimonials">
        <h2 class="section-title">Lo Que Dicen Nuestros Clientes</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <p class="testimonial-text">"La mejor experiencia gastronómica que he tenido. El lomo saltado es de otro
                    mundo. Totalmente recomendado."</p>
                <span class="testimonial-author">- Carlos Mendoza</span>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"Un ambiente espectacular y una atención de primera. Las pastas trufadas
                    superaron todas mis expectativas."</p>
                <span class="testimonial-author">- Laura Valenzuela</span>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">"Mis hamburguesas favoritas están aquí. La calidad de la carne y el sabor de
                    la salsa secreta son increíbles."</p>
                <span class="testimonial-author">- Diego Torres</span>
            </div>
        </div>
    </section>

    <!-- Nosotros (Historia) -->
    <section id="nosotros">
        <h2 class="section-title">Nuestra Historia</h2>
        <div class="about-content">
            <div class="about-text">
                <h3>Pasión por el Buen Sabor</h3>
                <p>Fundado en 2010, <strong>Sabor & Tradición</strong> nació del sueño de una familia apasionada por la
                    gastronomía. Empezamos como un pequeño local que ofrecía las recetas de nuestra abuela, recetas
                    guardadas celosamente por generaciones.</p>
                <p>Con el paso de los años, fusionamos esa tradición con técnicas de alta cocina contemporánea, creando
                    una experiencia única. Hoy en día, nos enorgullece ser reconocidos no solo por la calidad de
                    nuestros ingredientes, sino por el amor que ponemos en cada plato.</p>
                <p>Nuestro compromiso es brindarte un momento inolvidable, donde cada bocado te cuente una historia.</p>
            </div>
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                    alt="Interior del Restaurante">
            </div>
        </div>
    </section>

    <!-- Contacto (Mapa y Formulario) -->
    <section id="contacto" style="background-color: #1a1a1a;">
        <h2 class="section-title">Contáctanos</h2>
        <div class="contact-container">
            <div class="contact-form">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 1.8rem;">Reserva o Escríbenos</h3>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" class="form-control" placeholder="tu@correo.com" required>
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Mensaje / Detalles de Reserva</label>
                        <textarea id="mensaje" class="form-control" rows="4" placeholder="Escribe tu mensaje aquí..."
                            required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Mensaje</button>
                </form>
            </div>

            <div class="map-container">
                <!-- Mapa de Google Embebido (Ejemplo de Ubicación) -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126938.83401569305!2d-75.66699317585323!3d6.15582319225725!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e468200b39527a7%3A0xc3401140df03f7a!2sRestaurante!5e0!3m2!1ses!2sco!4v1716327000000!5m2!1ses!2sco"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-info">
            <h3 style="color: var(--primary); font-size: 1.5rem; margin-bottom: 1rem;">Sabor & Tradición</h3>
            <p>📍 Calle Falsa 123, Ciudad Gastronómica</p>
            <p>📞 +123 456 7890</p>
            <p>✉️ reservas@saborytradicion.com</p>
            <p style="margin-top: 2rem; font-size: 0.9rem; opacity: 0.7;">&copy; 2026 Sabor & Tradición. Todos los
                derechos reservados.</p>
        </div>
    </footer>

    <script src="{{ asset('js/ScriptHome.js') }}"></script>
</body>

</html>