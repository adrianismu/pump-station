@echo off
echo ========================================
echo Testing Pyramid - Pump Station System
echo ========================================
echo.

:: Check if MySQL is running
echo Checking MySQL service...
sc query mysql >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] MySQL service is not running!
    echo Please start MySQL service first.
    pause
    exit /b 1
)
echo [OK] MySQL service is running.

:: Create test database if not exists
echo.
echo Creating test database...
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pump_station_test;" 2>nul
if %errorlevel% equ 0 (
    echo [OK] Test database ready.
) else (
    echo [WARNING] Could not create test database. Please check MySQL credentials.
)

:: Run migrations for testing
echo.
echo Running migrations for testing environment...
php artisan migrate --env=testing --force

echo.
echo ========================================
echo Running Testing Pyramid
echo ========================================

:: Level 1: Unit Tests (Base)
echo.
echo [LEVEL 1] Running Unit Tests...
echo ----------------------------------------
php artisan test --testsuite=Unit --stop-on-failure
if %errorlevel% neq 0 (
    echo [FAILED] Unit tests failed! Stopping execution.
    pause
    exit /b 1
)
echo [PASSED] All unit tests passed!

:: Level 2: Integration Tests (Middle) 
echo.
echo [LEVEL 2] Running Integration Tests...
echo ----------------------------------------
php artisan test --testsuite=Integration --stop-on-failure
if %errorlevel% neq 0 (
    echo [FAILED] Integration tests failed! Stopping execution.
    pause
    exit /b 1
)
echo [PASSED] All integration tests passed!

:: Level 3: Feature Tests (Top)
echo.
echo [LEVEL 3] Running Feature Tests...
echo ----------------------------------------
php artisan test --testsuite=Feature --stop-on-failure
if %errorlevel% neq 0 (
    echo [FAILED] Feature tests failed! Stopping execution.
    pause
    exit /b 1
)
echo [PASSED] All feature tests passed!

:: Generate coverage report
echo.
echo ========================================
echo Generating Coverage Report...
echo ========================================
php artisan test --coverage --min=80

echo.
echo ========================================
echo Testing Pyramid Completed Successfully!
echo ========================================
echo.
echo Summary:
echo - Unit Tests: PASSED
echo - Integration Tests: PASSED  
echo - Feature Tests: PASSED
echo.
echo All tests completed successfully!
pause 