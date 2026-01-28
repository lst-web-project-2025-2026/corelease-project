#import "template.typ": *
#import "@preview/codly:1.3.0": *
#show: codly-init.with()

#show: project.with(
  title: "Corelease : Rapport technique (P.F.M.)",
  authors: (
    (name: "Bilal Houari", email: "houari.bilal@etu.uae.ac.ma", affiliation: "Sous-Groupe 1"),
    (name: "Ayoub Sacha", email: "ayoub.sacha@etu.uae.ac.ma", affiliation: "Sous-Groupe 1"),
    (name: "Ayman Amtot", email: "amtot.aymane@etu.uae.ac.ma", affiliation: "Sous-Groupe 1"),
    (name: "Mehdi Abdenbi", email: "", affiliation: "Sous-Groupe 1"),
  ),
  date: "Janvier 27, 2025",
)

#codly(stroke: 0.5pt + gray)

#show raw.where(block: true): it => {
  align(center)[#block(width: 90%, inset: 0.2em, breakable: false)[#it]]
}

#set table(
  fill: (_, y) => if y == 0 { rgb("#e4e4e4") },
)

#outline()

#pagebreak(weak: true)

= Introduction
== Aperçu du projet
Corelease est une plateforme d'orchestration de ressources internes conçue pour des environnements de recherche contrôlés. Le système facilite la gestion du cycle de vie d'actifs techniques hétérogènes, notamment des serveurs physiques en rack, des instances de machines virtuelles et des nœuds de réseau à haut débit. Il fournit une interface centralisée pour la gestion des stocks, la planification de la maintenance et l'allocation de ressources délimitée dans le temps.

== Objectifs techniques et périmètre
L'objectif technique principal de Corelease est d'établir une « Source de vérité unique » (Single Source of Truth - SSoT) déterministe pour l'allocation des ressources de l'installation. Le système impose le respect des politiques organisationnelles par le biais d'un processus de validation structuré et d'une hiérarchie d'autorisation basée sur les rôles.

Le périmètre opérationnel est défini par quatre piliers techniques fondamentaux :
+ #strong[Gestion des métadonnées d'inventaire] : Utilisation de schémas basés sur JSON pour gérer des spécifications techniques diverses et non relationnelles.
+ #strong[Prévention des conflits temporels] : Mise en œuvre d'un moteur algorithmique pour empêcher le chevauchement d'occupation des ressources entre les réservations et les fenêtres de maintenance.
+ #strong[Gouvernance et audibilité] : Suivi systématique des opérations administratives via une journalisation des différentiels d'état.
+ #strong[Notification et gestion d'état] : Mise à disposition d'un système d'alerte persistant et avec état pour les mises à jour opérationnelles et la diffusion d'informations système.

== Cadre technologique
L'architecture de l'application repose sur une pile conteneurisée conçue pour la performance, la modularité et la parité des environnements.

=== Framework applicatif : Laravel 12 (PHP 8.3)
Le backend utilise le framework Laravel 12, exploitant les propriétés typées et les optimisations de performance de PHP 8.
+ Cet environnement prend en charge l'abstraction par couche de service et la modélisation de relations complexes requises pour l'orchestration des ressources.

=== Persistance des données : MySQL 8.4
MySQL 8.4 fait office de système de gestion de base de données relationnelle (SGBDR) principal. Il a été sélectionné pour sa prise en charge native des types de données JSON et sa fiabilité transactionnelle. L'accès aux données est régi par une couche d'abstraction de service stricte afin de maintenir l'intégrité des données.

=== Infrastructure : Docker et Docker Compose
Le système est construit sur une architecture conteneurisée dès la phase initiale de développement. Docker Compose orchestre les dépendances entre le serveur d'application, le moteur de base de données et les outils administratifs secondaires, garantissant la cohérence entre les différents environnements de développement et de production.

=== Orchestration Frontend : Vite et Blade
La compilation des actifs est gérée par l'outil de construction Vite 6, qui facilite le remplacement de modules à chaud (Hot Module Replacement - HMR) pendant le développement. L'interface utilisateur est construite à l'aide d'un système de composants Blade modulaires, piloté par un moteur CSS basé sur des jetons afin de maintenir la cohérence du design.

= Architecture et structure
== Structure de répertoires spécifique au domaine
Corelease implémente une extension modulaire de la structure de répertoires standard de Laravel afin d'intégrer des modèles de conception orientés services (service-oriented design patterns).

```text
corelease/
├── app/
│   ├── Console/Commands/      # Tâches système automatisées
│   ├── Http/
│   │   ├── Controllers/       # Gestion et délégation des requêtes
│   │   └── Middleware/        # Intercepteurs transactionnels et de sécurité
│   ├── Models/                # Persistance des données et définition des relations
│   └── Services/              # Logique métier et couche d'abstraction
├── database/
│   ├── factories/             # Modèles de données synthétiques
│   └── seeders/               # Scripts de simulation d'environnement
├── resources/
│   ├── css/                   # Architecture de styles basée sur des jetons (tokens)
│   └── views/
│       └── components/ui/     # Bibliothèque de primitives d'interface utilisateur atomiques
└── vite.config.js             # Orchestration des actifs et pipeline de construction
```

== Abstraction architecturale : la couche de service
L'architecture se caractérise par le découplage de la logique métier des mécanismes de transport HTTP. Les opérations spécifiques au domaine sont encapsulées dans des classes de service dédiées.

=== Injection de la couche de service
Les contrôleurs font office de contrôleurs allégés (#emph[lean controllers]), déléguant les opérations complexes à des instances de services injectées.

```php
// Extrait de app/Http/Controllers/ManagerController.php
public function __construct(protected MaintenanceService $maintenanceService) {}

public function storeMaintenance(Request $request, Resource $resource)
{
    // Les détails de mise en œuvre sont délégués au service maintenanceService
    $this->maintenanceService->schedule(Auth::user(), $request->validated());
}
```

=== Avantages techniques
+ #strong[Centralisation de la logique] : Les règles métier sont définies en un point unique, garantissant la cohérence entre l'interface web et les commandes de l'interface en ligne de commande (CLI).
+ #strong[Gestion des effets de bord] : Les services orchestrent les opérations secondaires, telles que la journalisation d'audit et les déclencheurs de notification, sans introduire de complexité dans la couche des contrôleurs.
+ #strong[Extensibilité] : La séparation des préoccupations facilite la modification des algorithmes métier sans impact sur les couches de transport ou de persistance.

== Interceptions par middleware
L'état opérationnel est imposé par le biais de middlewares personnalisés. Le middleware `CheckMaintenanceMode` évalue l'état global du système stocké dans la table `Settings` afin d'empêcher tout accès non administratif pendant les périodes de maintenance à l'échelle de l'installation.

```php
// Aperçu de la logique du middleware
if ($this->systemService->isSystemLocked()) {
    if (!$request->is('login', 'logout', 'under-maintenance') && Auth::user()->role !== 'Admin') {
        return redirect()->route('maintenance.under');
    }
}
```

== Conception d'interface modulaire
Le frontend adopte une approche par composants correspondant aux principes du design atomique (#emph[atomic design]). Le répertoire `resources/views/components/ui` contient des composants Blade indépendants et sans état (#emph[stateless]), garantissant une cohérence visuelle et fonctionnelle sur l'ensemble de l'application.

= Persistance et modélisation des données
== Schéma relationnel et intégrité
Le schéma de la base de données de Corelease est conçu pour imposer une intégrité référentielle stricte. Des contraintes de clé étrangère sont implémentées sur l'ensemble des relations primaires afin de prévenir toute incohérence des données.

#figure(
  rotate(
    -90deg,
    reflow: true,
    box[
      #image("schema.svg")
    ],
  ),
  caption: [Le schéma de la base de données du projet],
)

=== Cartographie des relations
Le schéma utilise une structure hiérarchique où les entités `User` (Utilisateur) et `Resource` (Ressource) font office de nœuds principaux.

```php
// Définitions des relations dans app/Models/Reservation.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function resource()
{
    return $this->belongsTo(Resource::class);
}
```

== Modélisation avancée via le transtypage d'attributs
Le système exploite le transtypage d'attributs (#emph[attribute casting]) de l'ORM Eloquent pour gérer des types de données complexes et variables dans un contexte relationnel.

=== Implémentation de schémas JSON
Le modèle `Resource` gère diverses spécifications techniques par le biais du transtypage JSON, permettant le stockage de métadonnées hétérogènes dans une table unique sans rencontrer de problèmes de parcimonie des données (#emph[data sparsity]).

```php
// app/Models/Resource.php
protected $casts = [
    'specs' => 'array', // Sérialisation du JSON en tableaux PHP natifs
];

public function getCpuAttribute()
{
    // Accès direct aux propriétés stockées en JSON via le tableau
    return $this->specs['CPU Processor'] ?? 'Not Specified';
}
```

== Gestion des données temporelles
Corelease utilise la suppression logique (#emph[SoftDeletes]) pour les entités critiques afin de maintenir la conformité et un historique auditable au sein des environnements de recherche. Cette approche garantit que les enregistrements référencés dans les pistes d'audit demeurent persistants, même après une suppression administrative.

== Simulation d'environnement et peuplement
Le système implémente une stratégie de peuplement (#emph[seeding]) systématique via `DatabaseSeeder.php` afin de générer un environnement de développement représentatif de la production.

=== Génération procédurale de données
Plutôt que d'utiliser des chaînes de caractères aléatoires, le semeur (#emph[seeder]) emploie des réservoirs de nomenclature technique réelle.

```php
// Logique du seeder pour la génération de spécifications matérielles
foreach ($category->specs as $spec) {
    $specs[$spec] = match($spec) {
        'CPU Processor' => $pool['cpus'][array_rand($pool['cpus'])],
        'Physical RAM'  => $pool['rams'][array_rand($pool['rams'])],
        // ... (Sélection déterministe) ...
    };
}
```

=== Cohérence de l'état pendant le peuplement
Le processus de peuplement utilise les classes de la couche de service établies pour créer les enregistrements. Cela garantit que la base de données générée contient des données secondaires valides, telles que des journaux d'audit et des notifications, fournissant ainsi une simulation complète de l'historique opérationnel.

= Ingénierie backend
== Contrôleurs de domaine fonctionnels
L'architecture backend est organisée en contrôleurs de domaine spécifiques qui gèrent la validation des requêtes et l'orchestration des réponses.

=== Opérations de gestion (`ManagerController`)
Le `ManagerController` assure la gestion de l'état de l'inventaire et la planification de la maintenance. Il implémente des modèles de validation stricts, incluant un mécanisme déterministe d'analyse syntaxique (#emph[parsing]) des dates afin de gérer les divergences environnementales dans le formatage des chaînes de caractères.

```php
<?php
// Modèle de validation de date dans ManagerController.php
$safeParse = function ($str) {
    if (!$str) return null;
    $d = \DateTime::createFromFormat('Y-m-d', $str);
    return ($d && $d->format('Y-m-d') === $str) ? \Carbon\Carbon::instance($d) : null;
};
```

== Logique de la couche de service et intégrité transactionnelle
Les changements d'état affectant plusieurs modèles sont encapsulés au sein de la couche de service et exécutés dans le cadre de transactions de base de données afin de garantir l'atomicité des opérations.

=== Gestion du cycle de vie de la maintenance
Le `MaintenanceService` orchestre la planification et la clôture des fenêtres d'indisponibilité. Il garantit que les mises à jour du statut des ressources et les résolutions de conflits secondaires sont effectuées comme une unité de travail unique.

```php
<?php
// Implémentation de transaction dans MaintenanceService.php
public function schedule(User $manager, array $data): Maintenance
{
    return DB::transaction(function () use ($manager, $data) {
        $maintenance = Maintenance::create($data);

        // Propagation d'état conditionnelle
        if ($maintenance->status === 'In Progress') {
            $maintenance->resource->update(['status' => 'Maintenance']);
        }

        // Résolution automatisée des conflits via une coordination inter-services
        if ($data['resolve_conflicts'] ?? false) {
            $this->cancelOverlappingReservations($maintenance, $manager);
        }

        return $maintenance;
    });
}
```

== Détection algorithmique de conflits
Le `ReservationService` implémente un algorithme robuste pour empêcher le chevauchement des allocations de ressources. L'algorithme évalue l'intersection des plages temporelles entre les réservations approuvées et les fenêtres de maintenance actives.

```php
<?php
// Logique d'intersection de plages dans ReservationService.php
$query->whereBetween('start_date', [$start, $end])
      ->orWhereBetween('end_date', [$start, $end])
      ->orWhere(function ($q) use ($start, $end) {
          $q->where('start_date', '<=', $start)
            ->where('end_date', '>=', $end);
      });
```

Cette implémentation couvre toutes les permutations de collision temporelle, y compris l'inclusion totale et le chevauchement partiel.

== Synchronisation périodique de l'état
Le système maintient la précision temporelle via une commande Artisan planifiée, `app:refresh-statuses`. Cette commande fonctionne comme un observateur automatisé, effectuant la transition des états des modèles en fonction de l'écoulement du temps, sans nécessiter d'interaction utilisateur.

= Conception frontend et orchestration de l'interface utilisateur
== Méthodologie de conception et jetons (tokens)
L'interface utilisateur de Corelease repose sur une architecture CSS natif (Vanilla CSS) sur mesure. La cohérence visuelle est assurée par un système de propriétés CSS personnalisées (jetons de design) qui définissent les paramètres esthétiques de la plateforme.

=== Moteur de variables HSL dynamiques
La gestion des couleurs s'effectue via des jetons HSL (Teinte, Saturation, Luminosité). Cette approche facilite le contrôle programmatique du thème visuel.

```css
/* Jetons de thème global.css */
:root {
    --accent-h: 217; # Valeur de teinte pour l'identité de marque
    --accent-primary: hsl(var(--accent-h), 91%, 60%);
    --accent-glow: hsla(var(--accent-h), 91%, 60%, 0.15);
}
```

=== Persistance du style en temps réel
Une classe JavaScript `ThemeManager` orchestre l'application de ces jetons. Elle synchronise les préférences de l'utilisateur avec le stockage persistant du navigateur afin de maintenir des états d'interface cohérents entre les sessions.

```javascript
// Extrait de l'application du thème dans global.js
applyAccent(accent) {
    document.documentElement.style.setProperty("--accent-h", accent.h);
    document.documentElement.style.setProperty("--accent-s", `${accent.s}%`);
    document.documentElement.style.setProperty("--accent-l", `${accent.l}%`);
}
```

== Architecture de composants atomiques
L'interface est construite à partir de onze composants Blade fondamentaux. Ces composants sont des primitives sans état (#emph[stateless]) et découplées qui encapsulent une logique structurelle et visuelle spécifique.

=== Indication d'état basée sur les composants
Le composant `x-ui.status` traduit les indicateurs d'état du backend en marqueurs visuels sémantiques.

```html
    <span class="dot"></span>
    <span class="label">{{ ucfirst($status) }}</span>
```

Cette approche modulaire garantit que la notification d'état reste cohérente entre le catalogue et les tableaux de bord de gestion.

== Principes UX et densité d'information
Corelease adopte une approche minimaliste de l'architecture de l'information. Les erreurs de validation de formulaire sont agrégées dans un composant d'alerte centralisé en haut de l'interface, réduisant ainsi l'encombrement visuel et priorisant les retours système critiques.

== Implémentation de grille adaptative (responsive)
Les mises en page sont orchestrées à l'aide de CSS Grid et Flexbox pour assurer une adaptabilité sur diverses dimensions de fenêtres d'affichage (#emph[viewports]). Le catalogue utilise une grille `auto-fit` afin de maintenir une densité d'information optimale, des terminaux mobiles jusqu'aux environnements de bureau haute résolution.

= Infrastructure et outillage
== Architecture conteneurisée
La plateforme Corelease est implémentée selon une architecture multi-conteneurs. Celle-ci garantit la parité des environnements tout au long du cycle de vie du développement et isole les dépendances du système d'exploitation hôte.

=== Analyse au niveau des services
L'infrastructure est orchestrée via le fichier `compose.yaml`, qui définit trois services spécialisés.

==== Service d'application (`app`)
Basé sur une fondation Ubuntu 24.04, ce service fournit l'environnement d'exécution PHP 8.
+ Il utilise Apache 2 comme serveur web, configuré avec le module `mod_rewrite` pour le routage des requêtes propre à Laravel.

```dockerfile
# Extrait de la configuration Apache/PHP
RUN a2enmod rewrite && \
    sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
```

Afin de prendre en charge l'orchestration moderne des actifs (assets), le conteneur inclut un environnement d'exécution Node.js 22 managé, installé via NVM (#emph[Node Version Manager]).

==== Service de base de données (`db`)
Un moteur MySQL 8.4 assurant la persistance des données. Il utilise un volume persistant pour garantir la durabilité des enregistrements. Le service implémente un mécanisme de contrôle de santé (#emph[health-check]) pour s'assurer que l'initialisation de la couche applicative est différée jusqu'à ce que la base de données soit pleinement opérationnelle.

=== Avantages de la conteneurisation
+ #strong[Isolation des dépendances] : Le système hôte ne nécessite que l'environnement d'exécution Docker. Toutes les extensions PHP spécifiques (par exemple, `php-mysql`, `php-bcmath`) et les binaires sont encapsulés dans les images.
+ #strong[Stabilité environnementale] : Docker garantit des environnements rigoureusement identiques pour tous les développeurs, éliminant les incohérences liées aux configurations de l'OS hôte.

== Pipeline d'actifs : Vite 6
Vite 6 est utilisé comme outil de construction principal et serveur de développement, remplaçant les anciens regroupeurs d'actifs (#emph[bundlers]).

=== Remplacement de modules à chaud (HMR)
Vite est configuré pour fonctionner via le réseau Docker, prenant en charge l'injection d'actifs en temps réel pendant le développement par le biais d'un port dédié au service des actifs.

```javascript
// Extrait de la configuration vite.config.js
server: {
    host: '0.0.0.0', // Écoute sur l'interface du conteneur
    hmr: { host: 'localhost' } // Proxy vers la boucle locale de l'hôte
}
```

=== Empreinte numérique de build basée sur un manifeste
Lors des constructions pour la production, Vite effectue un hachage cryptographique de tous les actifs. Les noms de fichiers résultants contiennent des identifiants uniques (hashes), garantissant la neutralisation du cache (#emph[cache-busting]) et la livraison immédiate des feuilles de style et des scripts mis à jour aux clients.

== Flux de travail de développement : Dev Containers
Le projet inclut une configuration `.devcontainer` qui facilite le raccordement de l'environnement de développement intégré (EDI/IDE) au conteneur en cours d'exécution. Cette configuration expose au développeur les outils internes --- tels que l'interface en ligne de commande (CLI) Laravel Artisan et les extensions PHP spécialisées --- tout en maintenant une expérience de développement native et une isolation complète de l'environnement.

= Garde-fous opérationnels et systèmes spécialisés
== Moteur de prévention des conflits temporels
Le garde-fou opérationnel le plus rigoureux du système est le #strong[Moteur de prévention des conflits], situé dans le `ReservationService`. Ce moteur garantit que la plateforme impose un modèle d'occupation exclusive pour les nœuds matériels.

=== Vecteurs algorithmiques
Lorsqu'une réservation ou une fenêtre de maintenance est demandée, le moteur effectue une validation par rapport à quatre vecteurs de défaillance distincts :
+ #strong[Verrouillage global de l'installation] : Évalue si l'interrupteur `facility_maintenance` est actif. Si tel est le cas, toutes les actions de création non administratives sont bloquées.
+ #strong[Désactivation manuelle des ressources] : Vérifie le statut de la ressource cible. Les ressources dont l'état est `Disabled` (Désactivé) ne peuvent physiquement pas faire l'objet d'une réservation.
+ #strong[Chevauchement de maintenance] : Une requête basée sur des plages temporelles détecte les collisions avec les fenêtres d'indisponibilité existantes. L'algorithme utilise une vérification triple conditionnelle `OR` pour identifier les chevauchements partiels, les inclusions totales et les périodes où la nouvelle requête englobe une indisponibilité existante.
+ #strong[Conflit de réservation] : Une vérification de plage identique est effectuée par rapport aux baux approuvés et actifs afin d'empêcher les doubles réservations.

== Audit par différentiel d'état
La fiabilité est renforcée par un #strong[Système d'audit forensique] immuable.

=== Capture de différence basée sur JSON
Contrairement aux simples journaux d'événements, l' `AuditService` capture le différentiel d'état complet d'un objet. Lorsqu'un gestionnaire modifie le statut d'une ressource ou approuve une demande, le moteur d'audit enregistre :
- L'identifiant `acting_user_id` (utilisateur agissant).
- Le nom de l'événement (`event`), par exemple « Statut de la ressource mis à jour ».
- Un instantané JSON `old_values` des attributs AVANT l'événement.
- Un instantané JSON `new_values` des attributs APRÈS l'événement.

Ces données permettent aux administrateurs de réaliser une analyse post-incident avec une précision granulaire, en déterminant exactement quel attribut a été modifié et par qui.

== Synchronisation de l'état en temps réel
Pour maintenir l'exactitude du catalogue, le système implémente un relais de statut automatisé.
- Lorsqu'un enregistrement `Maintenance` passe à l'état « En cours » (In Progress), le statut de sa ressource parente (`Resource`) est automatiquement mis à jour à « Maintenance ».
- Lorsqu'une réservation (`Reservation`) devient « Active », sa disponibilité est verrouillée en interne.
- À l'achèvement ou à l'expiration de ces enregistrements, le statut de la ressource est automatiquement restauré à « Activé » (Enabled), sous réserve qu'aucun autre enregistrement conflictuel n'existe.

== Cycle de vie de validation et d'approbation
Le système impose une étape de validation (#emph[vetting]) obligatoire pour tous les nouveaux utilisateurs.
+ #strong[Inscription] : Un candidat fournit ses références professionnelles via le modèle `Application`.
+ #strong[Validation] : Un administrateur examine la justification et procède à une approbation ou à un rejet.
+ #strong[Promotion] : En cas d'approbation, l' `ApplicationService` gère la promotion sécurisée de l'enregistrement vers une identité `User` complète, en attribuant les rôles et en activant le compte.

Ce processus à plusieurs étapes garantit que seul le personnel vérifié obtient l'accès aux ressources techniques du centre de données.

= Sécurité, fiabilité et performance
== Protocoles de sécurité
Corelease implémente un modèle de sécurité multicouche afin de préserver la confidentialité des données et l'intégrité du système.

=== Authentification et hachage
Les identifiants sont hachés à l'aide de l'algorithme Bcrypt. La plateforme exploite la gestion de session sécurisée de Laravel, offrant une protection contre les vulnérabilités de type contrefaçon de requête intersites (CSRF) et d'autres vecteurs d'attaque web courants.

=== Logique de contrôle d'accès
Le système impose un modèle d'accès strict basé sur les rôles (RBAC), appliqué tant au niveau de l'interface utilisateur qu'aux couches de logique backend. Cette architecture garantit que les capacités opérationnelles --- telles que la modification de l'inventaire ou la validation des utilisateurs --- sont exclusivement restreintes au personnel autorisé.

== Fiabilité du système et sécurité transactionnelle
La fiabilité est assurée par l'exécution atomique des changements d'état critiques.

=== Intégration des transactions de base de données
Les mises à jour affectant plusieurs modèles --- telles que les approbations déclenchant la création de compte et la journalisation d'audit --- sont encapsulées dans des transactions de base de données. Cela garantit l'atomicité : soit l'intégralité des composants de l'opération aboutit, soit la transaction est totalement annulée (#emph[rolled back]) pour prévenir toute corruption de l'état des données.

=== Audit par différentiel d'état
L' `AuditService` capture des instantanés des états des entités avant et après chaque modification. Ces données différentielles sont stockées au format JSON, fournissant un registre forensique des actions administratives à des fins de conformité et d'audit.

```php
<?php
// Aperçu de la création d'un enregistrement d'audit
AuditLog::create([
    'event' => $event,
    'old_values' => $oldValues,
    'new_values' => $newValues,
    // ...
]);
```

== Stratégies d'optimisation des performances
Le maintien de temps de réponse optimaux est assuré par des optimisations architecturales au niveau des couches de données et d'actifs.

=== Performance des requêtes (Eager Loading)
Afin d'atténuer l'impact des requêtes à haute concurrence sur les performances, la plateforme utilise le chargement immédiat (#emph[eager loading]) pour l'ensemble des relations critiques. Cette méthode réduit la surcharge de la base de données de $O \( n \)$ à $O \( 1 \)$ lors des récupérations de données à grande échelle.

=== Efficacité du rendu frontend
Le recours au CSS natif (Vanilla CSS) et aux composants Blade sans état garantit une surcharge d'analyse (#emph[parsing]) minimale. L'utilisation de propriétés CSS bénéficiant de l'accélération matérielle assure une expérience utilisateur fluide sur des configurations matérielles hétérogènes.

= Améliorations potentielles et limitations techniques
== Évaluation de la pile technique actuelle
La pile technologique fondamentale de Corelease --- comprenant HTML, CSS et JavaScript natifs (Vanilla), ainsi que PHP côté serveur (Laravel) --- a été sélectionnée pour démontrer les principes fondamentaux du développement web. Toutefois, ce choix architectural introduit plusieurs limitations systémiques lorsqu'il est comparé aux standards industriels modernes.

=== Limitations des implémentations frontend natives
Le recours au JavaScript natif pour la gestion d'état (par exemple, le `ThemeManager`) manque de la réactivité et de la nature déclarative propres aux frameworks de composants modernes tels que React ou Vue.
- #strong[Surcharge liée à la manipulation du DOM] : Les mises à jour manuelles fréquentes du DOM augmentent le risque de produire un « code impératif spaghetti », rendant le frontend difficile à faire évoluer à mesure que la complexité de l'interface s'accroît.
- #strong[Synchronisation d'état] : En l'absence d'un magasin d'état centralisé (type Redux ou Vuex), la synchronisation des données entre des composants d'interface disparates devient sujette aux conditions de concurrence (#emph[race conditions]).

=== Contraintes du rendu traditionnel côté serveur (SSR)
Corelease dépend fortement du moteur Blade de Laravel pour l'orchestration des vues. Cette approche « centrée sur le serveur » (Server-Heavy) impose des compromis techniques :
- #strong[Interactivité latente] : Chaque action de navigation majeure nécessite une requête de document complète et un rechargement de page. Dans des environnements à haute concurrence, cela augmente la charge du serveur et introduit une latence perceptible pour l'utilisateur.
- #strong[Couplage monolithique] : L'intégration étroite entre le backend PHP et les vues HTML entrave la capacité à développer ou à déployer le frontend de manière indépendante.

== Pérennisation via des frameworks modernes
La transition vers une #strong[Application monopage (SPA)] ou un #strong[Framework hybride] (par exemple, Next.js ou React/Vue propulsé par Vite) offrirait des avantages opérationnels significatifs :
+ #strong[Réactivité côté client] : L'utilisation d'un DOM virtuel permettrait des mises à jour instantanées de l'interface sans rafraîchissement complet de la page, améliorant ainsi l'expérience du « catalogue en direct ».
+ #strong[Architecture API découplée] : En convertissant le backend Laravel en une API REST ou GraphQL sans état, le système pourrait prendre en charge simultanément plusieurs clients (Mobile, Web, CLI).
+ #strong[Optimisation avancée de la construction] : Les chaînes d'outils modernes offrent des capacités supérieures de #emph[tree-shaking] et de fractionnement du code (#emph[code-splitting]), réduisant davantage la charge utile initiale livrée au client.

== Évaluation conceptuelle critique
Au-delà des contraintes techniques, le postulat conceptuel de la plateforme --- un système de gestion de centre de données strictement réservé aux employés internes sans base de clients externes --- présente un périmètre opérationnel restreint. Ce modèle en « boucle fermée » limite la capacité du système à gérer la multi-location (#emph[multi-tenancy]), des cycles de facturation complexes ou des accords de niveau de service (SLA) destinés au public, qui sont des exigences standard pour les plateformes de gestion d'infrastructure de classe commerciale.

= Conclusion
== Synthèse de l'implémentation technique
La plateforme Corelease répond aux exigences d'un projet de groupe fondamental pour des étudiants universitaires s'initiant aux méthodologies de développement web. Elle démontre avec succès l'application de la conception de bases de données relationnelles, l'orchestration côté serveur via le framework Laravel et la mise en œuvre d'un environnement de développement conteneurisé.

== Contexte académique et progression
Bien que le projet atteigne ses objectifs pédagogiques immédiats, il se caractérise par plusieurs contraintes notables. L'implémentation actuelle est loin d'être achevée et manque de la pérennité architecturale (« future-proofing ») requise par les systèmes industriels modernes à grande échelle. Le recours à une pile monolithique traditionnelle constitue une base d'apprentissage, mais demeure insuffisant face aux complexités d'un moteur d'allocation d'entreprise en conditions réelles.

== Critique conceptuelle
Une évaluation critique du sujet du projet --- une plateforme de gestion de centre de données utilisée exclusivement par des employés, sans interaction avec des clients externes --- révèle une incohérence conceptuelle. L'absence d'une couche de service orientée client ou d'un modèle de multi-location (#emph[multi-tenancy]) diversifié simplifie la logique opérationnelle, mais réduit simultanément l'applicabilité pratique de la plateforme dans un contexte commercial de matériel en tant que service (HaaS).

== Évaluation finale
En conclusion, Corelease fournit un cadre de gouvernance des ressources robuste pour un niveau débutant. Son implémentation de modèles de conception formels et d'une logique déterministe offre une fondation fiable permettant aux étudiants de transiter vers des architectures web plus avancées, asynchrones et centrées sur le client à l'avenir.
