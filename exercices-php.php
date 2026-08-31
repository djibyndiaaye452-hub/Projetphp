<?php
/**
 * EXERCICES PHP — du basique à la BDD orientée objet
 * Un seul fichier, à découper en classes/fichiers au fur et à mesure.
 * Chaque exercice = squelette à compléter.
 */

declare(strict_types=1);

/* =====================================================================
 * NIVEAU 1 — Bases PHP (procédural)
 * ===================================================================== */

// --- 1.1 Gestionnaire de tâches (JSON) ---
// php exercices-php.php add "Titre" | list | done <id> | delete <id>
function loadTasks(string $file): array
{
    
    // TODO: lire $file, json_decode, retourner [] si absent
    if(!file_exists($file)){
        return [];
    }
    $contenue = file_get_contents($file) ;
    if($contenue === false || trim($contenue) === '' ){
        return [] ;
    }
    $tasks = json_decode($contenue, true) ;
    if(!is_array($tasks)){
        return [];
    }
    return $tasks ;
    
}

function saveTasks(string $file, array $tasks): void
{
    // TODO: json_encode + file_put_contents

   $json = json_encode($tasks , JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )  ;
   if ($json === false){
      throw new RuntimeException("Impossible d'encoder les tâches en JSON .") ;
   }
   if (file_put_contents($file , $json) === false ){
      throw new RuntimeException("Impossible d'ecrire dans le fichier : $file") ;
   } 
}

function addTask(array &$tasks, string $title): void
{
    // TODO: id, title, done=false, created_at
    $title = trim($title) ;
    if ($title === ''){
        throw new InvalidArgumentException("Le titre de la tâche ne peut pas être vide");
    }
    
    $Ids = array_column($tasks , 'id');
    $newId = $Ids === [] ? 1 : max($Ids);
    $tasks[] = [

        "id" => $newId ,
        "title"=> $title,
        "done"=> false ,
        "created_at" => date('Y-m-d H:i:s'),
    ] ;
}

function listTasks(array $tasks, ?bool $doneFilter = null): void
{
    // TODO: afficher, filtrer si $doneFilter non null
    $filtrer = $doneFilter === null ?
    $tasks :array_filter($tasks ,fn($task) => $task['done'] === $doneFilter) ;

    if($filtrer === []){
    echo"Aucune tâche à ajouter.\n";

        return;

    }
    foreach($filtrer as  $task ){ 
        $status = $task['done'] ? '[x]' :'[ ]';
        printf ("%s #%d - %s  (Créer le %s)",
        $status,
        $task['id'],
        $task['title'],
        $task['created_at']
        );
    
    }
}

function markDone(array &$tasks, int $id): void
{
    // TODO: array_filter/foreach, lever une erreur si id introuvable
    $trouver = false;
    foreach($tasks as &$task){
        if($task['id'] === $id ){
            $task['done'] = true ;
            $trouver = true ;
            break;
        }
    }
    unset($task);
    if(!$trouver){
        throw new InvalidArgumentException("La valeur de id: %d est introuvable", $id); 
    }

    
}

function deleteTask(array &$tasks, int $id): void
{
    // TODO: array_filter + array_values
    
   $filtrer = array_filter($tasks , fn(array $task) => $task['id'] !== $id);
     if( count($filtrer) === count($tasks)){
        throw new InvalidArgumentException("L'Id : $id est intouvable");
     }
    $tasks = array_values($filtrer);
    
}


// --- 1.2 Parser CSV → statistiques ---
function parseCsvStats(string $csvFile): array
{
    // TODO: fgetcsv en boucle, calculer total/moyenne/max par catégorie
    // retour attendu: ['categorie' => ['total' => ..., 'avg' => ..., 'max' => ...]]
    if(!file_exists($csvFile)){
        throw new RuntimeException("Fichier introuvable : $csvFile");
    }
    $fich = fopen($csvFile ,'r');
    if($fich === false ){
        throw new RuntimeException("Fichier :$csvFile non ouverte");
    }
    $data = [];
    $header = fgetcsv($fich);
    while(($row = fgetcsv($fich) ) !== false){
        if(count($row) < 2){
            continue;
        }
        $categorie =trim($row[0]);
        $montant = (float) $row[1];
        $data[$categorie][] = $montant;

    }
    fclose($fich);
    $stats = [] ;
    foreach($data as $categorie => $valeur){
        $stats[$categorie] =[
            "total"=> array_sum($valeur),
            "avg" => array_sum($valeur) / count($valeur),
            "max" => max($valeur),
        ];
    }

    return $stats;
}


// --- 1.3 Mini validateur de formulaire ---
function validate(array $data, array $rules): array
{
    // TODO: retourner un tableau d'erreurs
    // rules ex: ['email' => 'email', 'password' => 'min:8', 'age' => 'int|min:18']
    
    $errors = [];
    foreach($rules as $field => $ruleString){
        $value = $data[$field]  ?? null;
        $ruleField = explode('|', $ruleString) ;
            foreach($ruleField as $rule){
                [$ruleName , $param ] = array_pad(explode(':', $rule ,2 ),2, null) ;
                switch($ruleName){
                    case'required':
                        if($value === null || $value === ''){

                            $errors[$field][] = "le champs $field est requis";
                        }
                        break ;
                    case'email' :
                        if($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)){
                           $errors[$field][] = "Le champs $field doit être une adresse email .";
                        

                        }
                        break;
                    case'int' :
                        if($value !== null && filter_var($value,FILTER_VALIDATE_INT)){

                               $errors[$field][] = "Le champs $field doit être un nombre entier .";
                        }
                        break;
                    case'min' :
                        if($value !== null ){
                            $min= (int)$param;
                            if(is_numeric($value) && strlen($value) < $min){
                                $errors[$field][] = "Le champs $field doit contenir au moins $min caractères .";
                            }elseif(is_numeric($value) && (int)$value < $min){

                                $errors[$field][]= " Le champs $field doit être au moins $min ";
                            }
                        } 
                        break;
                    case'max' :
                        if($value !== null ){
                            $max= (int)$param;
                            if(is_numeric($value) && strlen($value) > $max){
                                $errors[$field][] = "Le champs $field doit contenir au moins $min caractères .";
                            }elseif(is_numeric($value) && (int)$value > $max){

                                $errors[$field][]= "Le champs $field doit être au plus $max ";
                            }
                        } 
                        break;       
                    default :
                        break;  
                }
            }
    }
    return $errors;
}


/* =====================================================================
 * NIVEAU 2 — POO de base
 * ===================================================================== */

// --- 2.1 Task orientée objet ---
final class Task
{
    public function __construct(
        public readonly int $id,
        private string $title,
        private bool $done = false,
        private readonly string $createdAt = ''
    ) {
    
        $id = (int)$id;
        if ($id <= 0) {
            throw new InvalidArgumentException("L'identifiant de la tâche doit être un entier positif");
        }
        $this->title = trim($title);
        if ($this->title === '') {
            throw new InvalidArgumentException("Le titre de la tâche ne peut pas être vide");   
        }
        $this->done = $done;
        $this->createdAt = $createdAt === '' ? date('Y-m-d H:i:s') : $createdAt; 
         
     }
    public function getId():int
    {
        return $this->id ;
    }
    public function getTitle(): string
    {
        return $this->title ;
    }
    public function getDone(): bool
    {

        return $this->done ;
    }
    public function getCreatedAt(): string
    {
        return $this->createdAt ;
    }
    public function setTitle( string $title): void
    {
        $this->title = $title;         
    }
    public function setDone( bool $done): void
    {
        $this->done = $done;         
    }
    public function setCreatedAt( string $createdAt): void
    {
        $this->createdAt = $createdAt;         
    }
    public function __toString(): string
    {
        $status = $this->done ? '[x]' : '[ ]';
        return sprintf("%s #%d - %s (Créer le %s)", $status, $this->id, $this->title, $this->createdAt);
    }

       
    public function markDone(): void
    {
        // TODO
        $this->setDone(true);
    }

    public function toArray(): array
    {
        // TODO
        return[
            'id' => $this->id,
            'title' => $this->title,
            'done' => $this->done,
            'created_at' => $this->createdAt,
        ] ;  
     }

    public static function fromArray(array $data): self
    {
        // TODO
        return new self(
            id: (int)$data['id'],
            title: (string)$data['title'],
            done: (bool)$data['done'],
            createdAt: (string)$data['created_at']
        );
    }
}

final class TaskCollection
{
    /** @var Task[] */
    private array $tasks = [];  
    public function add(Task $task): void
    {
        // TODO
        $this->tasks[] = $task;
    }

    public function remove(int $id): void
    {
        // TODO
        $this->tasks = array_filter($this->tasks, fn(Task $task) => $task->getId() !== $id);
    }

    public function filter(callable $predicate): array
    {
        // TODO: array_filter sur $this->tasks
        return array_filter($this->tasks, $predicate);
    }
}


// --- 2.2 Panier e-commerce ---
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $price
    ) {}
}

final class CartItem
{
    public function __construct(
        public readonly Product $product,
        public readonly int $quantity
    ) {}

    public function subtotal(): float
    {
        // TODO
        return $this->product->price * $this->quantity;
    }
}

interface DiscountStrategy
{
    public function apply(float $total): float;
}

final class PercentageDiscount implements DiscountStrategy
{
    public function __construct(private float $percent) {}

    public function apply(float $total): float
    {
        // TODO
        return $total - ($total * $this->percent / 100);
    }
}

final class Cart
{
    /** @var CartItem[] */
    private array $items = [];

    public function __construct(private ?DiscountStrategy $discount = null) {}

    public function addItem(Product $product, int $quantity): void
    {
        // TODO
        $this->items[] = new CartItem($product, $quantity);
    }

    public function total(): float
    {
        // TODO: somme des subtotal(), appliquer $this->discount si présent
        $total = array_reduce($this->items, fn($carry, CartItem $item) => $carry + $item->subtotal(), 0);
        if ($this->discount !== null) {
            $total = $this->discount->apply($total);
        }
        return $total;
    }
}


// --- 2.3 Formes géométriques ---
abstract class Shape
{
    abstract public function area(): float;
    abstract public function perimeter(): float;
}

final class Circle extends Shape
{
    public function __construct(private float $radius) {}
    public function area(): float { /* TODO */ }
    public function perimeter(): float { /* TODO */ }
}

final class Rectangle extends Shape
{
    public function __construct(private float $width, private float $height) {}
    public function area(): float { /* TODO */ }
    public function perimeter(): float { /* TODO */ }
}

final class Triangle extends Shape
{
    public function __construct(private float $base, private float $height, private float $a, private float $b, private float $c) {}
    public function area(): float { /* TODO: base * hauteur / 2 */ }
    public function perimeter(): float { /* TODO: a+b+c */ }
}


/* =====================================================================
 * NIVEAU 3 — POO + base de données (PDO)
 * ===================================================================== */

// --- 3.1 CRUD utilisateurs (PDO/SQLite) ---
final class UserRepository
{
    private PDO $pdo;

    public function __construct(string $dsn = 'sqlite:users.db')
    {
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initSchema();
    }

    private function initSchema(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL
            )
        ");
    }

    public function create(string $name, string $email): int
    {
        // TODO: requête préparée INSERT, retourner lastInsertId
    }

    public function find(int $id): ?array
    {
        // TODO: SELECT préparé, fetch
    }

    public function update(int $id, string $name, string $email): void
    {
        // TODO: UPDATE préparé
    }

    public function delete(int $id): void
    {
        // TODO: DELETE préparé
    }

    public function all(): array
    {
        // TODO: SELECT *
    }
}


// --- 3.2 Migration Task : JSON → SQLite (même interface) ---
interface TaskRepositoryInterface
{
    public function save(Task $task): void;
    public function findAll(): array;
    public function find(int $id): ?Task;
    public function delete(int $id): void;
}

final class TaskNotFoundException extends \RuntimeException {}

final class JsonTaskRepository implements TaskRepositoryInterface
{
    public function __construct(private string $file) {}

    public function save(Task $task): void
    {
        // TODO: charger toutes les tasks, remplacer/ajouter, réécrire le JSON
    }

    public function findAll(): array
    {
        // TODO
    }

    public function find(int $id): ?Task
    {
        // TODO
    }

    public function delete(int $id): void
    {
        // TODO
    }
}

final class SqliteTaskRepository implements TaskRepositoryInterface
{
    private PDO $pdo;

    public function __construct(string $dsn = 'sqlite:tasks.db')
    {
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                done INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )
        ");
    }

    public function save(Task $task): void
    {
        // TODO: INSERT ... ON CONFLICT(id) DO UPDATE (ou logique insert/update séparée)
    }

    public function findAll(): array
    {
        // TODO: SELECT * puis Task::fromArray sur chaque ligne
    }

    public function find(int $id): ?Task
    {
        // TODO: throw TaskNotFoundException si absent, ou retourner null selon convention choisie
    }

    public function delete(int $id): void
    {
        // TODO
    }
}

final class TaskManager
{
    public function __construct(private TaskRepositoryInterface $repository) {}

    public function addTask(string $title): Task
    {
        // TODO: valider $title, créer Task, save()
    }

    public function completeTask(int $id): void
    {
        // TODO: find(), throw TaskNotFoundException si null, markDone(), save()
    }

    public function removeTask(int $id): void
    {
        // TODO
    }

    public function listTasks(?bool $doneFilter = null): array
    {
        // TODO
    }
}


// --- 3.3 Blog avec relations (1-N) ---
final class Author
{
    public function __construct(public readonly int $id, public readonly string $name) {}
}

final class Post
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly Author $author
    ) {}
}

final class PostRepository
{
    public function __construct(private PDO $pdo) {}

    public function initSchema(): void
    {
        // TODO: CREATE TABLE authors, CREATE TABLE posts avec FK author_id
    }

    public function findByAuthor(int $authorId): array
    {
        // TODO: JOIN posts + authors, hydrater des objets Post/Author
    }
}


/* =====================================================================
 * NIVEAU 4 — Architecture avancée
 * ===================================================================== */

// --- 4.1 Mini ORM (Active Record simplifié) ---
abstract class Model
{
    protected static PDO $pdo;
    protected static string $table;

    public static function setConnection(PDO $pdo): void
    {
        static::$pdo = $pdo;
    }

    public function save(): void
    {
        // TODO: via get_object_vars(), INSERT ou UPDATE selon présence d'un id
    }

    public static function find(int $id): ?static
    {
        // TODO: SELECT, hydrater via propriétés dynamiques ou constructeur
    }

    public static function where(string $column, mixed $value): array
    {
        // TODO: requête dynamique préparée
    }
}


// --- 4.2 API REST CRUD (sans framework) ---
final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        // TODO: stocker [method, path] => handler
    }

    public function dispatch(string $method, string $uri): void
    {
        // TODO: matcher la route, appeler le handler, sinon 404 JSON
    }
}

// Exemple d'utilisation attendue (à implémenter):
// $router->add('GET', '/tasks', fn() => jsonResponse($taskManager->listTasks()));
// $router->add('POST', '/tasks', fn() => jsonResponse($taskManager->addTask($_POST['title'])));

function jsonResponse(mixed $data, int $status = 200): void
{
    // TODO: header('Content-Type: application/json'), http_response_code($status), echo json_encode($data)
}


// --- 4.3 Tests + transactions ---
// Voir fichier séparé tests/TaskManagerTest.php (PHPUnit) — non exécutable ici.
// Exemple de transaction à implémenter dans un OrderService :
final class OrderService
{
    public function __construct(private PDO $pdo) {}

    public function placeOrder(int $productId, int $quantity): void
    {
        // TODO:
        // $this->pdo->beginTransaction();
        // try { décrémenter stock, insérer commande; $this->pdo->commit(); }
        // catch (\Throwable $e) { $this->pdo->rollBack(); throw $e; }
    }
}




