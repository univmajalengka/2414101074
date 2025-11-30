
        // --- LOGIKA NAVBAR MOBILE ---
        const btnMobile = document.getElementById('mobile-menu-btn');
        const menuMobile = document.getElementById('mobile-menu');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        btnMobile.addEventListener('click', () => {
            menuMobile.classList.toggle('hidden');
        });

        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                menuMobile.classList.add('hidden');
            });
        });

        // --- LOGIKA SLIDER BANNER ---
        const slider = document.getElementById('slider');
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        let currentIndex = 0;
        const totalSlides = slides.length;
        let slideInterval;

        function showSlide(index) {
            if (index >= totalSlides) currentIndex = 0;
            else if (index < 0) currentIndex = totalSlides - 1;
            else currentIndex = index;
            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        nextBtn.addEventListener('click', () => {
            showSlide(currentIndex + 1);
            resetInterval();
        });

        prevBtn.addEventListener('click', () => {
            showSlide(currentIndex - 1);
            resetInterval();
        });

        function startAutoSlide() {
            slideInterval = setInterval(() => {
                showSlide(currentIndex + 1);
            }, 5000);
        }

        function resetInterval() {
            clearInterval(slideInterval);
            startAutoSlide();
        }

        startAutoSlide();

        // --- LOGIKA SCROLL PEMESANAN ---
        function selectPackage(packageName) {
            const selectBox = document.getElementById('packageSelect');
            selectBox.value = packageName;
            document.getElementById('pemesanan').scrollIntoView({ behavior: 'smooth' });
        }

        // --- LOGIKA SUBMIT FORM ---
        function handleBooking(event) {
            event.preventDefault();
            alert("Terima kasih! Data pemesanan terkirim.");
        }
    