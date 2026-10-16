### **Backend**
php artisan xxx
#
composer xxx
#
php artisan test
#
docker start swc-mysql + php artisan serve
###
php artisan make:model Sede -mfc        # model + migration + factory + controller
#
php artisan make:controller SedeController --api
#
php artisan migrate
#
php artisan migrate:fresh --seed        **DEVELOPMENT ONLY**
#
php artisan migrate:rollback
#
php artisan route:list
#
php artisan tinker                      # consola para testar modelos

###
**INICIAR SERVIDOR**
docker start swc-mysql                  # só se o container estiver parado
#
cd ~/dev/swc/src/api && php artisan serve    # terminal 1: http://127.0.0.1:8000
#
cd ~/dev/swc/src/app && npm run dev          # terminal 2: http://localhost:5173

###
**PUSH/PULL**
  # 1. actualizar main
git checkout main
#
git pull
#
git checkout -b feature/modelo-sedes     # um branch por Issue

# 2. commits
git status # verificar q esta distinto
#
git add src/api                          # o src/app
#
git commit -m "msg"

# 3. enviar
  git push

###
