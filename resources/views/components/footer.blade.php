<footer class="footer">
    <div class="container-footer">
        <div class="footer-info">
            <a href="/" class="brand">
                <x-ui.logo size="28px" />
                <span class="brand-name">Corelease</span>
            </a>
            <p>High-performance computational resource management for authorized research groups.</p>
        </div>

        <div class="footer-links">
            <h4>System Resources</h4>
            <ul>
                <li><a href="/catalog">Resource Catalog</a></li>
                <li><a href="/maintenance">Maintenance Schedule</a></li>
                <li>
                    @auth
                        <a href="/dashboard">Dashboard</a>
                    @else
                        <a href="/#status-checker">Application Tracker</a>
                    @endauth
                </li>
            </ul>
        </div>

        <div class="footer-copy">
            <p>&copy; {{ date('Y') }} Corelease. Internal Service Allocation Platform.</p>
        </div>
    </div>
</footer>
