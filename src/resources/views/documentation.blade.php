<!DOCTYPE html>
<html lang="fr">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>ControllerGenerate</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;

            background: #f8fafc;

            color: #0f172a;

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                sans-serif;
        }

        button,
        select,
        input {
            font: inherit;
        }

        .page {
            max-width: 1200px;

            margin: 0 auto;

            padding: 40px 24px;
        }

        /* --------------------------------------------------
         HEADER
        -------------------------------------------------- */

        .header {
            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            margin-bottom: 32px;
        }

        .header h1 {
            margin: 0;

            font-size: 28px;

            letter-spacing: -0.5px;
        }

        .header p {
            margin: 7px 0 0;

            color: #64748b;

            font-size: 14px;
        }

        .summary {
            display: flex;

            gap: 8px;
        }

        .summary-item {
            padding: 7px 11px;

            border: 1px solid #e2e8f0;

            background: white;

            border-radius: 8px;

            font-size: 12px;

            color: #475569;
        }

        /* --------------------------------------------------
         MODEL
        -------------------------------------------------- */

        .models {
            display: flex;

            flex-direction: column;

            gap: 10px;
        }

        .model {
            background: white;

            border: 1px solid #e2e8f0;

            border-radius: 10px;

            overflow: hidden;
        }

        .model-header {
            width: 100%;

            border: 0;

            background: white;

            padding: 17px 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            cursor: pointer;

            text-align: left;
        }

        .model-header:hover {
            background: #f8fafc;
        }

        .model-main {
            display: flex;

            align-items: center;

            gap: 12px;
        }

        .model-icon {
            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 8px;

            background: #f1f5f9;

            color: #334155;

            font-size: 14px;

            font-weight: 700;
        }

        .model-name {
            font-weight: 650;

            font-size: 15px;
        }

        .model-table {
            margin-top: 3px;

            color: #94a3b8;

            font-size: 12px;

            font-family: monospace;
        }

        .model-meta {
            display: flex;

            align-items: center;

            gap: 7px;
        }

        .badge {
            padding: 4px 8px;

            border-radius: 999px;

            background: #f1f5f9;

            color: #64748b;

            font-size: 11px;
        }

        .chevron {
            color: #94a3b8;

            font-size: 14px;

            transition: transform .15s;
        }

        .model.open .chevron {
            transform: rotate(180deg);
        }

        /* --------------------------------------------------
         CONTENT
        -------------------------------------------------- */

        .model-content {
            display: none;

            border-top: 1px solid #e2e8f0;

            padding: 24px;
        }

        .model.open .model-content {
            display: block;
        }

        .content-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 1.6fr) minmax(280px, 1fr);

            gap: 24px;
        }

        .section {
            border: 1px solid #e2e8f0;

            border-radius: 9px;

            overflow: hidden;

            background: white;
        }

        .section-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 13px 15px;

            border-bottom: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 12px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .05em;

            color: #64748b;
        }

        /* --------------------------------------------------
         ATTRIBUTES
        -------------------------------------------------- */

        .attributes {
            padding: 4px 15px;
        }

        .attribute {
            min-height: 50px;

            display: grid;

            grid-template-columns:
                minmax(0, 1fr) 150px;

            align-items: center;

            gap: 20px;

            border-bottom: 1px solid #f1f5f9;
        }

        .attribute:last-child {
            border-bottom: 0;
        }

        .attribute-name {
            display: flex;

            align-items: center;

            gap: 8px;

            font-family: monospace;

            font-size: 13px;
        }

        .attribute-name small {
            font-family: inherit;

            color: #94a3b8;

            font-size: 10px;
        }

        .type-select {
            width: 100%;

            border: 1px solid #e2e8f0;

            background: #f8fafc;

            border-radius: 7px;

            padding: 7px 9px;

            color: #334155;

            font-size: 12px;

            cursor: pointer;
        }

        .type-select:focus {
            outline: none;

            border-color: #94a3b8;
        }

        .add-attribute {
            width: 100%;

            padding: 11px 15px;

            border: 0;

            border-top: 1px solid #e2e8f0;

            background: white;

            color: #475569;

            font-size: 12px;

            text-align: left;

            cursor: pointer;
        }

        .add-attribute:hover {
            background: #f8fafc;

            color: #0f172a;
        }

        /* --------------------------------------------------
         RELATIONS
        -------------------------------------------------- */

        .relations {
            padding: 5px 15px;
        }

        .relation {
            padding: 11px 0;

            border-bottom: 1px solid #f1f5f9;
        }

        .relation:last-child {
            border-bottom: 0;
        }

        .relation-name {
            font-family: monospace;

            font-size: 13px;
        }

        .relation-details {
            margin-top: 4px;

            color: #64748b;

            font-size: 11px;
        }

        /* --------------------------------------------------
         CONTROLLER
        -------------------------------------------------- */

        .controller {
            padding: 15px;
        }

        .controller-name {
            font-family: monospace;

            font-size: 13px;

            margin-bottom: 12px;
        }

        .methods {
            display: flex;

            flex-wrap: wrap;

            gap: 5px;
        }

        .method {
            padding: 5px 8px;

            border-radius: 6px;

            background: #f1f5f9;

            color: #475569;

            font-family: monospace;

            font-size: 11px;
        }

        /* --------------------------------------------------
         ROUTES
        -------------------------------------------------- */

        .routes {
            padding: 5px 15px;
        }

        .route {
            display: flex;

            gap: 8px;

            align-items: center;

            padding: 9px 0;

            border-bottom: 1px solid #f1f5f9;
        }

        .route:last-child {
            border-bottom: 0;
        }

        .method-label {
            min-width: 45px;

            font-family: monospace;

            font-size: 10px;

            font-weight: 700;
        }

        .route-uri {
            font-family: monospace;

            color: #475569;

            font-size: 11px;
        }

        /* --------------------------------------------------
         EMPTY
        -------------------------------------------------- */

        .empty {
            padding: 16px;

            color: #94a3b8;

            font-size: 12px;
        }

        /* --------------------------------------------------
         ADD ATTRIBUTE
        -------------------------------------------------- */

        .new-attribute {
            display: none;

            padding: 15px;

            border-top: 1px solid #e2e8f0;

            background: #f8fafc;
        }

        .new-attribute.open {
            display: block;
        }

        .new-attribute-form {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr) 150px auto;

            gap: 8px;
        }

        .new-attribute input,
        .new-attribute select {
            border: 1px solid #cbd5e1;

            border-radius: 7px;

            padding: 8px 10px;

            background: white;

            font-size: 12px;
        }

        .new-attribute button {
            border: 0;

            border-radius: 7px;

            padding: 8px 14px;

            background: #0f172a;

            color: white;

            cursor: pointer;

            font-size: 12px;
        }

        /* --------------------------------------------------
         RESPONSIVE
        -------------------------------------------------- */

        @media (max-width: 850px) {

            .content-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;

                gap: 15px;
            }

        }

        @media (max-width: 600px) {

            .page {
                padding: 20px 12px;
            }

            .model-content {
                padding: 12px;
            }

            .attribute {
                grid-template-columns: 1fr;

                gap: 5px;

                padding: 12px 0;
            }

            .new-attribute-form {
                grid-template-columns: 1fr;
            }

        }
    </style>

</head>


<body>

    <div class="page">


        {{-- HEADER --}}

        <header class="header">

            <div>

                <h1>
                    ControllerGenerate
                </h1>

                <p>
                    Laravel API project documentation
                </p>

            </div>


            <div class="summary">

                <div class="summary-item">
                    {{ $project['summary']['models'] ?? 0 }}
                    Models
                </div>

                <div class="summary-item">
                    {{ $project['summary']['controllers'] ?? 0 }}
                    Controllers
                </div>

                <div class="summary-item">
                    {{ $project['summary']['routes'] ?? 0 }}
                    Routes
                </div>

            </div>

        </header>


        {{-- MODELS --}}

        <div class="models">

            @foreach($project['models'] ?? [] as $model)

            <div
                class="model"
                data-model="{{ $model['name'] }}">

                {{-- MODEL HEADER --}}

                <button
                    type="button"
                    class="model-header"
                    onclick="toggleModel(this)">

                    <div class="model-main">

                        <div class="model-icon">
                            M
                        </div>

                        <div>

                            <div class="model-name">
                                {{ $model['name'] }}
                            </div>

                            <div class="model-table">
                                {{ $model['table'] }}
                            </div>

                        </div>

                    </div>


                    <div class="model-meta">

                        <span class="badge">
                            {{ count($model['fillable'] ?? []) }}
                            attributes
                        </span>

                        <span class="badge">
                            {{ count($model['relations'] ?? []) }}
                            relations
                        </span>

                        <span class="chevron">
                            ▼
                        </span>

                    </div>

                </button>


                {{-- MODEL CONTENT --}}

                <div class="model-content">

                    <div class="content-grid">


                        {{-- LEFT --}}

                        <div>


                            {{-- ATTRIBUTES --}}

                            <section class="section">

                                <div class="section-header">

                                    <span class="section-title">
                                        Attributes
                                    </span>

                                </div>


                                <div class="attributes">

                                    @foreach(
                                    $model['fillable'] ?? []
                                    as $attribute
                                    )

                                    @php

                                    $column = collect(
                                    $model['migration']['columns'] ?? []
                                    )->firstWhere(
                                    'name',
                                    $attribute
                                    );

                                    $type =
                                    $column['type']
                                    ?? 'string';

                                    @endphp


                                    <div class="attribute">

                                        <div class="attribute-name">

                                            {{ $attribute }}

                                            @if(
                                            $column &&
                                            ($column['nullable'] ?? false)
                                            )

                                            <small>
                                                nullable
                                            </small>

                                            @endif

                                        </div>


                                        <select
                                            class="type-select"
                                            data-model="{{ $model['name'] }}"
                                            data-attribute="{{ $attribute }}">

                                            @foreach([
                                            'string',
                                            'text',
                                            'integer',
                                            'bigInteger',
                                            'unsignedInteger',
                                            'decimal',
                                            'float',
                                            'double',
                                            'boolean',
                                            'date',
                                            'datetime',
                                            'timestamp',
                                            'time',
                                            'json',
                                            'uuid',
                                            'foreignId',
                                            ] as $availableType)

                                            <option
                                                value="{{ $availableType }}"
                                                @selected(
                                                $availableType===$type
                                                )>
                                                {{ $availableType }}
                                            </option>

                                            @endforeach

                                        </select>

                                    </div>

                                    @endforeach

                                </div>


                                {{-- ADD ATTRIBUTE --}}

                                <button
                                    type="button"
                                    class="add-attribute"
                                    onclick="toggleAddAttribute(this)">
                                    + Add attribute
                                </button>


                                <div class="new-attribute">

                                    <!-- <div class="new-attribute-form">

                                        <input
                                            type="text"
                                            placeholder="attribute_name">

                                        <select>

                                            <option value="string">
                                                string
                                            </option>

                                            <option value="text">
                                                text
                                            </option>

                                            <option value="integer">
                                                integer
                                            </option>

                                            <option value="bigInteger">
                                                bigInteger
                                            </option>

                                            <option value="boolean">
                                                boolean
                                            </option>

                                            <option value="decimal">
                                                decimal
                                            </option>

                                            <option value="date">
                                                date
                                            </option>

                                            <option value="datetime">
                                                datetime
                                            </option>

                                            <option value="json">
                                                json
                                            </option>

                                            <option value="foreignId">
                                                foreignId
                                            </option>

                                        </select>

                                        <button
                                            type="button"
                                            onclick="submitNewAttribute(this, {modelPath: '{{ $model['path'] ?? '' }}',migrationPath: '{{ $model['migration']['path'] ?? '' }}',controllerPath: '{{ $model['controller']['path'] ?? '' }}'})">
                                            Add
                                        </button>

                                    </div> -->
                                    <div
                                        class="new-attribute-form"
                                        data-model-path="{{ $model['path'] ?? '' }}"
                                        data-migration-path="{{ $model['migration']['path'] ?? '' }}"
                                        data-controller-path="{{ $model['controller']['path'] ?? '' }}">
                                        <input
                                            type="text"
                                            class="new-attribute-name"
                                            placeholder="attribute_name">

                                        <select class="new-attribute-type">
                                            <option value="string">string</option>
                                            <option value="text">text</option>
                                            <option value="integer">integer</option>
                                            <option value="bigInteger">bigInteger</option>
                                            <option value="boolean">boolean</option>
                                            <option value="decimal">decimal</option>
                                            <option value="date">date</option>
                                            <option value="datetime">datetime</option>
                                            <option value="json">json</option>
                                            <option value="foreignId">foreignId</option>
                                        </select>

                                        <button
                                            type="button"
                                            onclick="submitNewAttribute(this)">
                                            Add
                                        </button>
                                    </div>

                                </div>

                            </section>

                        </div>


                        {{-- RIGHT --}}

                        <div>


                            {{-- RELATIONS --}}

                            <section class="section">

                                <div class="section-header">

                                    <span class="section-title">
                                        Relations
                                    </span>

                                </div>


                                <div class="relations">

                                    @forelse(
                                    $model['relations'] ?? []
                                    as $relation
                                    )

                                    <div class="relation">

                                        <div class="relation-name">

                                            {{ $relation['name'] }}

                                        </div>

                                        <div class="relation-details">

                                            {{ $relation['type'] }}

                                            →

                                            {{ $relation['model'] }}

                                        </div>

                                    </div>

                                    @empty

                                    <div class="empty">
                                        No relations detected.
                                    </div>

                                    @endforelse

                                </div>

                            </section>


                            <br>


                            {{-- CONTROLLER --}}

                            <section class="section">

                                <div class="section-header">

                                    <span class="section-title">
                                        Controller
                                    </span>

                                </div>


                                @if($model['controller'] ?? null)

                                <div class="controller">

                                    <div class="controller-name">

                                        {{ $model['controller']['name'] }}

                                    </div>


                                    <div class="methods">

                                        @foreach(
                                        $model['controller']['methods'] ?? []
                                        as $method
                                        )

                                        <span class="method">

                                            {{ $method }}

                                        </span>

                                        @endforeach

                                    </div>

                                </div>

                                @else

                                <div class="empty">
                                    No controller detected.
                                </div>

                                @endif

                            </section>


                            <br>


                            {{-- ROUTES --}}

                            <section class="section">

                                <div class="section-header">

                                    <span class="section-title">
                                        Routes
                                    </span>

                                </div>


                                <div class="routes">

                                    @forelse(
                                    $model['routes'] ?? []
                                    as $route
                                    )

                                    <div class="route">

                                        <span class="method-label">

                                            {{ $route['method'] }}

                                        </span>

                                        <span class="route-uri">

                                            {{ $route['uri'] }}

                                        </span>

                                    </div>

                                    @empty

                                    <div class="empty">
                                        No routes detected.
                                    </div>

                                    @endforelse

                                </div>

                            </section>

                        </div>

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </div>


    <script>
        /*
         * Ouvre / ferme un modèle.
         */
        function toggleModel(button) {

            const model =
                button.closest('.model');

            model.classList.toggle('open');

        }


        /*
         * Ouvre le formulaire "Add attribute".
         */
        function toggleAddAttribute(button) {

            const form =
                button.nextElementSibling;

            form.classList.toggle('open');

        }


        /*
         * Pour l'instant le changement de type
         * ne modifie pas encore le fichier.
         *
         * On branchera ici notre futur
         * AttributeChangeService.
         */
        document
            .querySelectorAll('.type-select')
            .forEach(select => {

                select.addEventListener(
                    'change',
                    function() {

                        console.log({
                            model: this.dataset.model,

                            attribute: this.dataset.attribute,

                            type: this.value
                        });

                    }
                );

            });

        async function submitNewAttribute(button) {
            const form = button.closest('.new-attribute-form');
            const nameInput = form.querySelector('.new-attribute-name');
            const typeSelect = form.querySelector('.new-attribute-type');

            const attribute = nameInput.value.trim();
            const type = typeSelect.value;

            const modelPath = form.dataset.modelPath;
            const migrationPath = form.dataset.migrationPath;
            const controllerPath = form.dataset.controllerPath;

            if (!attribute) {
                alert('Veuillez entrer un nom d\'attribut.');
                return;
            }

            button.disabled = true;

            // Récupération du token CSRF depuis la meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch('/generate/attributes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        attribute: attribute,
                        type: type,
                        model_path: modelPath,
                        migration_path: migrationPath,
                        controller_path: controllerPath
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur (' + response.status + '): ' + (data.message || 'Impossible d\'ajouter l\'attribut.'));
                }
            } catch (error) {
                console.error('Erreur technique :', error);
                alert('Erreur de communication avec le serveur. Vérifiez la console (F12).');
            } finally {
                button.disabled = false;
            }
        }
    </script>

</body>

</html>
