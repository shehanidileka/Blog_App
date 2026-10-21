
<footer class="footer pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <x-logo />
                <p class="muted small mt-3">Stories, tutorials and recipes shared with love.</p>
                <div class="social">
                    <a href="https://www.facebook.com/Shehani De Silva" target="_blank" rel="noopener noreferrer" class="s-facebook" aria-label="Facebook" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.linkedin.com/in/shehani-de-silva-91bb69370/" target="_blank" rel="noopener noreferrer" class="s-linkedin" aria-label="LinkedIn" title="LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="https://wa.me/94753779823?text=Hello%20MyBlog" target="_blank" rel="noopener noreferrer" class="s-whatsapp" aria-label="WhatsApp" title="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    <a href="https://github.com/shehanidileka" target="_blank" rel="noopener noreferrer" class="s-github" aria-label="GitHub" title="GitHub">
                        <i class="bi bi-github"></i>
                    </a>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <h6>Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><a href="{{ route('home') }}">Home</a></li>
                    @guest
                        <li class="mb-1"><a href="{{ route('login') }}">Login</a></li>
                        <li class="mb-1"><a href="{{ route('register') }}">Register</a></li>
                    @endguest
                    <li class="mb-1"><a href="#">Contact</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3">
                <h6>Categories</h6>
                <ul class="list-unstyled small">
                    @foreach(($navCategories ?? collect())->take(6) as $c)
                        <li class="mb-1"><a href="{{ route('categories.show', $c->slug) }}">{{ $c->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-3">
                <h6>Join us</h6>
                <p class="muted small">Register and write your own posts.</p>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-dash"><i class="bi bi-speedometer2 me-1"></i>My Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-dash">Register</a>
                @endauth
            </div>
        </div>

        <hr style="border-color: var(--border)">
        <p class="text-center muted small mb-0">© {{ date('Y') }} MyBlog. All rights reserved.</p>
    </div>
</footer>