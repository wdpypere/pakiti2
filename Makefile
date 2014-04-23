all: rpm

rpm: 
	rpmbuild -ba pakiti2.spec
