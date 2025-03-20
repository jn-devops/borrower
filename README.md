# Homeful Borrower Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jn-devops/borrower.svg?style=flat-square)](https://packagist.org/packages/jn-devops/borrower)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jn-devops/borrower/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jn-devops/borrower/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jn-devops/borrower/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jn-devops/borrower/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jn-devops/borrower.svg?style=flat-square)](https://packagist.org/packages/jn-devops/borrower)

---

## Description

The **Homeful Borrower Package** is a modular PHP package designed for handling borrower-related functionalities. It integrates financial calculations, lending institution rules, and employment types to assess a borrower's loan eligibility.

This package provides:
- Borrower age and validation rules
- Employment classification and income tracking
- Loan eligibility computations based on disposable income
- Co-borrower support with joint income evaluation
- Lending institution configurations with maximum loan terms
- Payment mode management

---

## Features

### 📌 **Age Management**
- Retrieve and format borrower age
- Set borrower age and birthdate
- Validate minimum and maximum borrowing ages

### 💰 **Employment & Income**
- Assign employment type (Private, Government, OFW, Business)
- Compute gross monthly income and disposable income
- Support for multiple sources of income

### 🏦 **Lending Institutions & Loan Terms**
- Define lending institutions with borrowing rules
- Determine maximum loan term based on institution settings
- Handle institution-specific age restrictions

### 🤝 **Co-Borrowers & Joint Income**
- Add co-borrowers and calculate total joint disposable income
- Identify the youngest and oldest co-borrower

### 💳 **Payment & Contact Information**
- Set payment modes (Online, Salary Deduction, Over-the-Counter)
- Assign and retrieve borrower contact details
- Validate and format borrower mobile numbers

---

## Installation

To install via Composer, run:

```bash
composer require jn-devops/borrower
```

---

## Usage

### 🔹 Creating a Borrower Instance

```php
use Homeful\Borrower\Borrower;
use Illuminate\Support\Carbon;
use Brick\Money\Money;

$borrower = new Borrower();
$borrower->setBirthdate(Carbon::parse('1999-03-17'));
$borrower->setGrossMonthlyIncome(Money::of(15000.0, 'PHP'));
```

### 🔹 Setting Employment Type

```php
use Homeful\Borrower\Enums\EmploymentType;

$borrower->setEmploymentType(EmploymentType::LOCAL_PRIVATE);
```

### 🔹 Adding Co-Borrowers

```php
$coBorrower = (new Borrower())->setBirthdate(Carbon::parse('2001-03-17'))->setGrossMonthlyIncome(Money::of(14000.0, 'PHP'));

$borrower->addCoBorrower($coBorrower);
```

### 🔹 Calculating Disposable Income

```php
$monthlyDisposableIncome = $borrower->getMonthlyDisposableIncome();
```

### 🔹 Lending Institution & Loan Term

```php
use Homeful\Borrower\Classes\LendingInstitution;

$lendingInstitution = new LendingInstitution('hdmf');
$borrower->setLendingInstitution($lendingInstitution);

$maxTerm = $borrower->getMaximumTermAllowed();
```

---

## Testing

Run the tests with:

```bash
composer test
```

---

## Author

- **Lester B. Hurtado**  
  Email: [devops@joy-nostalg.com](mailto:devops@joy-nostalg.com)  
  GitHub: [jn-devops](https://github.com/jn-devops)

---

## License

This package is open-source software licensed under the **MIT License**. See the [License File](LICENSE.md) for details.
