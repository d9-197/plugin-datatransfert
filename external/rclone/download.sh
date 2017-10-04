#!/bin/sh
cd `dirname $0`
VERSION=v1.38
case `uname -m` in
  x86)
    PLATFORM=386
	;;
  x86_64)
    PLATFORM=amd64
	;;
  aarch64)
    PLATFORM=arm64
	;;
  armv6l|armv7l)
    PLATFORM=arm
	;;
  mips)
    PLATFORM=mips
	;;
  mipsle)
    PLATFORM=mipsle
	;;
esac
rm -rf rclone-$VERSION-linux-$PLATFORM.zip rclone-$VERSION-linux-$PLATFORM
wget https://downloads.rclone.org/rclone-$VERSION-linux-$PLATFORM.zip
unzip rclone-$VERSION-linux-$PLATFORM.zip
mv rclone-$VERSION-linux-$PLATFORM/rclone .
rm -rf rclone-$VERSION-linux-$PLATFORM.zip rclone-$VERSION-linux-$PLATFORM