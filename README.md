# laravel-curd-by-command
##   Everything in one command 

The mini-wizard package is a Laravel package designed to streamline the creation of various components necessary for a Laravel application's CRUD operations. It automates the generation of migrations, controllers, models, and other related files, saving developers time and reducing the risk of errors.

## Key Features
    1. Automated File Generation: Automatically generates migration files, controllers, and models based on user input.
    2. Modular Design: Utilizes a modular approach to handle different components (migrations, controllers, models) through dedicated classes.
    3. Stub-Based Templates: Uses stub files as templates for the generated files, making it easy to customize the output.
    4. Traits for Reusability: Implements traits (ModuleKeys and StubPaths) to manage module identifiers and stub paths, enhancing code reusability and reducing errors.
    5. User Interaction: Provides a command-line interface for users to specify the model and fields, and to confirm the creation of various files
## pakage structure
            src/
        ├── bootstrap/
        │   └── config.php
        ├── Commands/
        │   └── WizardCommand.php
        ├── Services/
        │   ├── MigrationCreation.php
        │   ├── BaseCreation.php
        │   ├── FileModifier.php
        │   └── AllFunctionalityClass.php  
        └── Traits/
        │   ├── ConsoleHelper.php
        │   ├── ModuleKeys.php
        │   └── PathManager.php
        ├── template/
        │   ├── sidebar
        │         ├── 2024_05_31_085644_create_n_sidebars_table.php
        │         ├── NSidebarModel.php
        │         └── nSidebarSeeder.php 
        │   ├── stubs
        │         ├── create.stub
        │         ├── edit.stub
        │         ├── factory.stub
        │         ├── index.stub
        │         ├── migration.stub
        │         ├── model.stub
        │         ├── parent-service-class.stub
        │         ├── resource-controller.stub
        │         ├── route.stub
        │         ├── seeder.stub
        │         ├── service-class.stub
        │         ├── show.stub
        │         ├── store-request.stub
        │         └── update-request.stub

### Some notes
    1. Don't use name field. Always try to use title field instead of name which will use to session message. and try to use title field for every table.
    1. Just one name only model name will use for the all name. Example shows for "(Photo), (OrderItem)".
    2. Table(migration) name will be snake case and plural of model. Example:- (photos),(order_items).
    3. Controller name will be  (PhotoController), (OrderItemController).
    4. Permissions name will be  (Show Photo, Create Photo, Edit Photo, Delete Photo), (Show OrderItem, Create  OrderItem, Edit OrderItem, Delete OrderItem).
    5. Requests name will be  (StorePhotoRequest, UpdatePhotoRequest), (StoreOrderItemRequest, UpdateOrderItemRequest).
    6. Every migration default contain title, status, serial field. You can modified these from the migration stub file.
    7. For automatice implementation of seeder or factory you have to use  in the DatabaseSeeder.php 
    8. in parent module relationship added after  '];'

### Improveable note
    1. every datatype validation will added
    2. every inpute filed in create and edit file will added

### Warning
    1. You got a error that "This model '---' already exist." Rather than deleting your model,  you have to choose another name. If you want to delete the model you have to delete the migration, controller, requests, views, seeder,factories also with this same name.

