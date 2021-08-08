# Homework assignment for VIA SMS GROUP
The purpose of this project is to demonstrate the knowledge of [Laravel](https://laravel.com/) framework.

#
## Task list
Using the [Laravel](https://laravel.com/) PHP framework, create a basic "virtual wallet" application that satisfies the
following user stories:
- As a User, I can sign up for a User account, so that I can access the application.
- As a User, I can log in to my account.
- As a User, I can log out of my account.
- As a User, I can create a virtual Wallet.
- As a User, I can view a list of my virtual Wallet.
- As a User, I can rename my virtual Wallet.
- As a User, I can delete a virtual Wallet.
- As a User, I can add a Transaction to a virtual Wallet.
- As a User, I can see all the Transactions in my virtual Wallet.
- As a User, I can delete Transaction from a virtual Wallet.
- As a User, I can mark Transaction as Fraudulent.
- As a User, I see the total sum of Transactions in a virtual Wallet separated into incoming and outgoing transactions. 

#
## Set up
- Set up [PHP 7.4](https://www.php.net/releases/7_4_0.php) and database of choice. For testing purposes I used
  [MySQL 5.7](https://dev.mysql.com/downloads/mysql/5.7.html)
- Use your own web server or use `php artisan serve`. For the purpose of this example, I will use http://localhost:8000

#
## Usage
You can use the DB seeder to generate fake data, but they will be inprecise. So for full experience. Let's do things
manually.

### Create user
- Navigate to http://localhost:8000 home page and observe that you are required to log in to use the application;
- use the top navigation bar to navigate to registration page;
- once in http://localhost:8000/register page, provide a name, e-mail and password. Make sure the passwords match;
- after successful registration you are logged in and the system has already created a starting virtual wallet for you.

### Log out
- If you are logged in, you will see a "Log out" button in the top navigation bar. Use it to log out;

### Log in
- Navigate to http://localhost:8000 home page and observe that you are required to log in to use the application;
- if you are already logged in and try to access the http://localhost:8000/login page, you will be redirected back to
home page which is the virtual wallet list; 

### Creating a virtual wallet
- In the wallet list page, you will see the "+ Add wallet" button. Press button to navigate to wallet creation form;
- once in wallet creation form, enter a name for your wallet;
- if the wallet name exists, you will see an error;
- if the wallet is created, you will see a success message;

### Browsing your virtual wallets
- By default an new wallet will be given a unique ID, which is the wallet address;
- at the beginning the wallet will have 0.00€ balance on it;
- if you have created more than 20 virtual wallets, you will see a that they are now listed into pages;
- from the wallet list you can press "View" to see details of a single wallet;
- the details will provide a name, address, date when the virtual wallet was created and current balance;
- press "Back" to go back to the list view.

### Editing virtual wallets
- You can edit the wallet name by pressing "Edit" from the single wallet view or from the list view;
- once in edit mode, you can now give the your virtual wallet a new name;
- make sure the name is unique. Don't worry about other user wallet names, it has be unique only for you.

### Deleting virtual wallets
- You can delete the wallet name by pressing "Delete" from the single wallet view or from the list view;
- The virtual wallet is soft-deleted in case you made a huge mistake. However undeleting feature is not implemented for
users, though. And since there are no admin users who could do it, you can manipulate the `deleted_at` in DB by reseting
it. However all transactions associated with it will be deleted permanently.

### Creating transactions
- Since by default you have 0.00€ on your virtual wallet, it's time to add some money to it;
- in the top navigation bar press "Transactions";
- in transaction list view press "+ Add transaction";
- observe the two columns "From" and "To";
- you can transfer money from your virtual wallets to any other virtuall wallets. Even your own. Except if the wallet is
the same;
- or you can transfer money from a credit card to your or any other virtual wallets;
- you can also transfer money from wallet to another credit card;
- but you cannot transfer money from credit card to another credit card;
- since you do not have any money on the virtual wallet, select "From: Credit card";
- obseve you can now enter the card credentials;
- enter a fake name, enter a fake card number, expiry date and security code;
- make sure to enter correct card number consisting of 4x4 digits and valid expiry date. The date cannot be in the past;
- enter amount you want to transfer;
- on the right side, observe you can only select a transaction type "Wallet";
- type the virtual wallet address (copy it from you list of wallets);
- if you wish to transfer money to another user wallet, you can do that too;
- virtual wallet address has to exist in the system;
- if everything was successful, you will be navigated back to you transaction list and you will see a table with
transactions;
- now you have money in virtual wallet, you can now transfer money to another wallet or a credit card;
- to do so press "+ Add transaction" again;
- on the left side choose "From: Wallet" and select a wallet;
- you will see the wallet address and balance on the right side;
- on the right side you can choose to transfer money to another wallet or a credit card;
- if you choose a wallet, make sure the address exists. It can be your own, but not the same one;
- if you choose a credit card, type a fake the card holder name and a fake card number. Make sure you follow the card
syntax 4x4 digit number;
- if you wish to cancel a transaction press "Cancel" and you will be navigated back to transaction list view.

### Browsing transactions
- Navigate to "Transactions" in the top navigation bar;
- once you have made enough transactions, you can see some data in the transaction table;
- by default you will see all incoming and outgoing transactions from all wallets;
- you can narrow down the results by selecting a specific wallet or displaying only incoming or only outgoing
transactions;
- at the end of the table you will see the totals of your transactions;
- transactions made from and to credit cards are masked with asterisks. Only the last 4 digits are visible.

### Marking transactions as fraudulent
- Navigate to "Transactions" in the top navigation bar;
- if you have an suspicious incoming or outgoing transaction, you can mark it was fraudulent by pressing "Fraudulent"
button in the transaction list view;
- if this transaction was from another virtual wallet, this action will be visible visible to sender as well. The sender
will too see that his transaction was marked as fraudulent;
- if you change your mind, you can undo this by pressing the button again.

### Deleting transactions
- Navigate to "Transactions" in the top navigation bar;
- to delete a transaction press "Delete" in the list view;
- the transaction will be deleted but only for you;
- if this transaction was from a wallet to another wallet, the receiver will still see this transaction until he/she
deletes this transaction from his/her list.

#
## Missing features, future improvements, known issues etc.
- Transactions should not be deletable, they should be archived. Deleting transactions simply messes up the whole
balance and total count;
- MethodNotAllowedHttpException is not handled well. Probably because the project is in development mode. For example
using GET request to http://localhost:8000/logout that allows only POST should show a 404 error page or maybe redirect
to home page if possible.
- users cannot edit profiles, change e-mails and delete profiles or even change password. For the purposes of this task,
decided to create only registration and authentication using custom controller. Otherwise I would've used
[Laravel Breeze](https://github.com/laravel/breeze), but I didn't want to componentize every view, layout and button
etc. I could use partitially components and partitially just blades, but that would've been incosistent.
- add a fancy landing page, show e-mail and password logins on top corner, use maybe even log in via ajax. Many things
to improve here;
- would be be nice to add a "remember me". Currently there is no cookie stored;
- would be nice to see all history from certain periods. Like a table or graph view;
- Istead of making transactions instant, they should be confirmed first;
- add a lot more JS to edit wallets and make transactions smooth and seamless;
- allow user to sort wallets A-Z or Z-A. Add a filter for dates and other fields. For example view transactions of the
past month;
- add checkboxes in list view to delete multiple transactions or wallets;
- get column length from DB and put it in the maxlength input field instead of hardcoding them with numbers 255;
- instead of multiple forms, try to make one `<form>` and change the action depening on what button is pressed;
- single Wallet view does not have transactions table binded. It could have an ajax call to transactions controller and
return a JSON. Same call could be executed in transactions view as well;
- after transaction is made, user is redirected back to the wallet selected before. Now it just redirects back to all
wallet transactions
- would be nice to have an option to choose destination one of user's wallets, instead of typing an address manually;
- `<select>` buttons don't have a style for now. Actually all design could be improved;
- add DB transactions if something goes wrong like deleting a wallet and transactions are stuck, the procedure could be
rolled back;
- transaction table seeder doesn't create two records in DB one for each user if transactions are from wallet to wallet.
Resulting in inprecise data;
- feature tests are iffy and they cannot access certain error responses. Tests are incomplete and are probably written
incorrectl,y and a lot are missing.
