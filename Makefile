## SETUP PROJECT, please read Readme.md in root of project

## =============================================== CONFIGURE ME ==================================##

TEMP_DIR = ./temp
LOG_DIR = ./log
# If you don't need create webtemp dir set false
WEBTEMP_DIR = false

CONFIG_PATH = ./app/config
LOCAL_CONFIG_EXAMPLE_NAME = config.local.neon.example
LOCAL_CONFIG_NAME = config.local.neon
## ============================================== /CONFIGURE ME ==================================##

LOCAL_CONFIG_EXAMPLE = $(CONFIG_PATH)/$(LOCAL_CONFIG_EXAMPLE_NAME)
LOCAL_CONFIG = $(CONFIG_PATH)/$(LOCAL_CONFIG_NAME)

## Do anything :-)
dev: clean_cache
	composer install -o --no-dev; \

	return 0;

prelive: dev

live: prelive


setup: create_dirs
	@if [ -s $(LOCAL_CONFIG_EXAMPLE) ] && [ $(LOCAL_CONFIG_NAME) != false ] && [ ! -a $(LOCAL_CONFIG) ]; then \
		echo "Creating local config in $(LOCAL_CONFIG), you may need update this file.";\
		cp $(LOCAL_CONFIG_EXAMPLE) $(LOCAL_CONFIG); \
	fi

	@if [ -s composer.json ]; then \
		echo "Running Composer install";\
		composer install; \
	fi

## Cleaning cache
clean_cache:
	echo "Cleaning caches";\

	@rm -rf $(TEMP_DIR)/*; \

	@if [ -d $(WEBTEMP_DIR) ]; then \
		rm -rf $(WEBTEMP_DIR)/*; \
	fi

## Creating needed directories
create_dirs:
	@if [ ! -d $(TEMP_DIR) ]; then \
		echo "Making temp directory $(TEMP_DIR) witch chmod 0777"; \
		mkdir -p $(TEMP_DIR); \
		chmod 0777 $(TEMP_DIR);\
	fi \

	@if [ ! -d $(LOG_DIR) ]; then \
		echo "Making log directory $(LOG_DIR) witch chmod 0777"; \
		mkdir -p $(LOG_DIR); \
		chmod 0777 $(LOG_DIR);\
	fi \

	@if [ ! -d $(WEBTEMP_DIR) ] && [ $(WEBTEMP_DIR) != false ] ; then \
		echo "Making webtemp directory $(WEBTEMP_DIR) witch chmod 0777"; \
		mkdir -p  $(WEBTEMP_DIR); \
		chmod 0777 $(WEBTEMP_DIR);\
	fi \