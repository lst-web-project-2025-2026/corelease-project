<div class="ui-table-wrapper {{ $wrapperClass ?? '' }}">
    <table class="ui-table {{ $tableClass ?? '' }}">
        @isset($thead)
            <thead>
                <tr>
                    {{ $thead }}
                </tr>
            </thead>
        @endisset
        
        <tbody>
            {{ $slot }}
        </tbody>

        @isset($tfoot)
            <tfoot>
                <tr>
                    {{ $tfoot }}
                </tr>
            </tfoot>
        @endisset
    </table>
</div>

<style>
.ui-table-wrapper {
    width: 100%;
    overflow-x: auto;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 1rem;
    box-shadow: var(--shadow-sm);
}

.ui-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 0.9375rem;
}

.ui-table th {
    padding: var(--space-lg) var(--space-xl);
    background: var(--bg-tertiary);
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border-color);
}

.ui-table td {
    padding: var(--space-lg) var(--space-xl);
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.ui-table tr:last-child td {
    border-bottom: none;
}

.ui-table tbody tr {
    transition: background var(--transition-fast);
}

.ui-table tbody tr:hover {
    background: rgba(var(--accent-h), var(--accent-s), var(--accent-l), 0.02);
}

.ui-table .text-muted {
    font-size: 0.8125rem;
    color: var(--text-secondary);
}

.ui-table .text-right {
    text-align: right;
}

.ui-table .text-center {
    text-align: center;
}

.ui-table strong {
    color: var(--text-primary);
    font-weight: 600;
}
</style>
