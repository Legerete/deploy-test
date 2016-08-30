# Legerete core

## What this make

* SetUp namespace mapping for \Legerete\CRM namespace
* Create Legerete\CRM\UIForm\FormFactory
* Basic SecuredPresenter (`Legerete\CRM\Presenters\SecuredPresenter`)

## Basic usage

To your `config.neon` add:
```yaml
extensions:
	core: Legerete\CRM\DI\CoreExtension
```