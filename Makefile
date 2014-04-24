all: rpm

clean:
	rm -rf /tmp/pakiti_rpm_root
	rm -f pakiti2-client-*.rpm pakiti2-server-*.rpm
rpm:
	mkdir -p /tmp/pakiti_rpm_root /tmp/pakiti_rpm_root/BUILD /tmp/pakiti_rpm_root/RPMS /tmp/pakiti_rpm_root/RPMS/i386 /tmp/pakiti_rpm_root/SOURCES /tmp/pakiti_rpm_root/SPECS  /tmp/pakiti_rpm_root/SRPMS /tmp/pakiti_rpm_root/BUILDROOTDIR
	mkdir /tmp/pakiti_rpm_root/SOURCES/pakiti2-2.1.5/
	cp -r * /tmp/pakiti_rpm_root/SOURCES/pakiti2-2.1.5/
	tar czf /tmp/pakiti_rpm_root/SOURCES/pakiti2-2.1.5.tar.gz -C /tmp/pakiti_rpm_root/SOURCES pakiti2-2.1.5
	cp pakiti2.spec /tmp/pakiti_rpm_root/SPECS
	rpmbuild --define '_sourcedir /tmp/pakiti_rpm_root/SOURCES' --define '_builddir /tmp/pakiti_rpm_root/BUILD' --define '_rpmdir /tmp/pakiti_rpm_root/RPMS' --define '_buildrootdir /tmp/pakiti_rpm_root/BUILDROOTDIR' --define '_topdir /tmp/pakiti_rpm_root' --define '_srcrpmdir /tmp/pakiti_rpm_root/SRPMS' -ba pakiti2.spec
	cp /tmp/pakiti_rpm_root/RPMS/noarch/* .
