# Makefile

ROOT         = $(shell pwd)
MANIFESTS    = $(ROOT)/manifest
PACKAGES     = $(shell egrep -hv '^(\#.*)?$$' $(MANIFESTS))
CHANNELS     = $(ROOT)/channels
CHANNEL_LIST = $(shell egrep -hv '^(\#.*)?$$' $(CHANNELS))
STATIC       := $(shell find static -type f -name '*.tgz')
BIN_DIR      = $(ROOT)/bin
PEAR_BIN     = $(ROOT)/bin/pear
PHP_BIN      = $(shell which php)
CONF         = $(ROOT)/pear.conf
BIN          = $(shell test -f $(PEAR_BIN) && echo $(PEAR_BIN) || which pear)
OPTS         = -c $(CONF)
PEAR         = $(BIN) $(OPTS)
PEARVERSION  = '1.9.1'
OS           := $(shell uname)
.   PHONY    = all pear tmp replace install clean clean-all static

# PEAR Buildup
all: | clean-all pear install local clean ignore replace

conf: $(CONF)
$(CONF):
	pear config-create $(ROOT) $@ > /dev/null
	$(PEAR) config-set bin_dir $(ROOT)/pear/bin
	$(PEAR) config-set cache_dir $(ROOT)/pear/tmp/cache
	$(PEAR) config-set doc_dir $(ROOT)/pear/tmp/doc
	$(PEAR) config-set download_dir $(ROOT)/pear/tmp/download
	$(PEAR) config-set temp_dir $(ROOT)/pear/tmp/tmp
	$(PEAR) config-set test_dir $(ROOT)/pear/tmp/test
	$(PEAR) config-set www_dir $(ROOT)/pear/tmp/www
	for i in $(CHANNEL_LIST) ; do      \
        $(PEAR) channel-discover $$i ; \
    done

pear: $(CONF)
	$(PEAR) channel-update -f pear.php.net
	#$(PEAR) config-set preferred_mirror us.pear.php.net
	$(PEAR) install -o channel://pear.php.net/PEAR-$(PEARVERSION)

replace: replace-$(OS)
	@# This will find any file with a absolute path in it and replace
	@# it with a __ROOT__ and move it to end in .in
replace-Linux:
	grep -rl "$(ROOT)\|$(PHP_BIN)" pear/ | xargs -I{} mv {} {}.in
	find $(ROOT)/pear/ -name "*.in" | xargs -n1 -I{} sed -i"" -e "s&$(ROOT)/pear&__PEAR_ROOT__&g" -e "s&$(PHP_BIN)&__PHP__&g" {}

replace-Darwin:
	grep -rl "$(ROOT)\|$(PHP_BIN)" pear/ | xargs -I{} mv {} {}.in
	find $(ROOT)/pear/ -name "*.in" | xargs -n1 -I{} sed -i "" -e "s&$(ROOT)/pear&__PEAR_ROOT__&g" -e "s&$(PHP_BIN)&__PHP__&g" {}

ignore:
	# This will find any file with an absolute path and add it to a .gitignore
	# So when the file gets translated by m4, the original will be ignored by git
	grep -rl "$(ROOT)\|$(PHP_BIN)" pear/ | sort > .gitignore

install:
	@echo "Installing packages:"
	-@for PACKAGE in $(PACKAGES); do \
		echo "$$PACKAGE"; \
		$(PEAR) install -f -o $$PACKAGE; \
	done

local:
	@echo "Installing local packages:"
	-@for PACKAGE in $(STATIC); do \
		echo "$$PACKAGE"; \
		$(PEAR) install -f -o $$PACKAGE; \
	done

clean-cache:
	rm -rf $(ROOT)/pear/tmp/*

clean:
	# Clean out "temporary" files.  Really these are files which pear installs which we don't care about
	rm -rf $(ROOT)/pear/tmp $(CONF) $(ROOT)/pear/php/.channels $(ROOT)/pear/php/.registry $(ROOT)/pear/php/pearcmd.php $(ROOT)/pear/php/peclcmd.php
	find $(ROOT)/pear -name '.*' -type f -exec rm -f {} \;

clean-all:
	# Clean out everything, only use when you want to rebuild the pear install
	rm -rf $(ROOT)/pear $(CONF)

ec:
	make clean-all pear install local clean-cache
