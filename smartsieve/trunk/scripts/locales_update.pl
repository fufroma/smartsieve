#!/usr/bin/perl -w
#####################################################################
# $Id$
# Name:        locales_update.pl
# Author:      S Grier Dec 2003.
# Usage:       ./locales_update.pl [locale1] [locale2]
# Description: Generate new translation files for locale(s). If no 
#              locales are passed on command line, we will process all
#              locales under conf/locales. This scripts extracts the 
#              necessary strings from the source files and creates new 
#              translation files as conf/locales/*/strings.php.new.
# Copyright 2002-2004 Stephen Grier <stephengrier@users.sourceforge.net>
# See the inclosed NOTICE file for conditions of use and distribution.
######################################################################

use strict;

# where is this script relative to SmartSieve base?
my $ss_base = '..';
# a list of directories containing the source files we want to search for
# strings to translate. only files with the corresponding extensions will be searched.
my %dirs = ('.' => ['.php'],
            'include' => ['.inc', '.js'],
            'lib' => ['.lib', '.php'],
            'lib/Crypt' => ['.lib', '.php']
            );
# an array containing source files to search for strings.
my @files;
# an array to hold extracted strings.
my %strings;
# the location of language files.
my $locale_path = 'conf/locale';
# Get any locales passed on command line.
my @locales = @ARGV;

# get the list of source files to search.
foreach my $dir (keys %dirs) {
    opendir(DIR,"$ss_base/$dir") or die "can't opendir $ss_base/$dir: $!";
    my $file;
    while (defined($file = readdir(DIR))) {
        foreach my $extn (@{$dirs{$dir}}){
            my $len = length($extn);
            if (substr($file, -$len) eq $extn) {
                push(@files, "$ss_base/$dir/$file");
            }
        }
    }
    closedir(DIR);
}


# search each file for SmartSieve::text strings.
foreach my $file (@files){
    open(FILE, "<$file") or die "Can't open $file: $!\n";
    while (<FILE>) {
        if ($_ =~ /SmartSieve::text\(.+?\)/) {
            my @matches = $_ =~ /SmartSieve::text\((.+?)\)/g;
            # there may be more than one SmartSieve::text call on this line.
            foreach my $fstring (@matches){
                # want just the string, no args.
                # also, include the quotes, single or double, as we need this.
                my $char = substr($fstring,0,1);
                $fstring=~ /^(.+?[^\\]$char)/;
                # if string isn't already in %strings with other quotes, add.
                if ($char eq '"'){
                    if (!defined($strings{"'" . substr($1,1,-1) . "'"})) {
                        $strings{$1} = '';
                    }
                } else {
                    # $char = "'"
                    if (!defined($strings{'"' . substr($1,1,-1) . '"'})) {
                        $strings{$1} = '';
                    }
                }
            }
        }
    }
    close(FILE);
}

# %strings should now contain all the strings we want in our locales files.
# For each locale, open strings.php and see if a translation currently exists.

# If no locales passed on command line, process all locales.
if (scalar(@locales) == 0) {
    opendir(DIR,"$ss_base/$locale_path") or die "Can't opendir $ss_base/$locale_path: $!";
    @locales = readdir(DIR);
    closedir(DIR);
}

foreach my $locale (@locales) {
    next if ($locale eq '.' || $locale eq '..');
    print "Processing $locale\n";
    open(STRINGS, "< $ss_base/$locale_path/$locale/strings.php") or next;
    my %lstrings = %strings;
    while (<STRINGS>) {
        my $line = $_;
        if ($line =~ /\s*(['"].+['"])\s*=>\s*(['"].+['"])/) {
            my $char = substr($1,0,1);
            if (defined($lstrings{$1})) {
                print "+ Found translation for $1: $2\n";
                $lstrings{$1} = $2;
            }
            elsif ($char eq "'" && defined($lstrings{'"'.substr($1,1,-1).'"'})) {
                print "+ Found translation for $1: $2\n";
                $lstrings{'"'.substr($1,1,-1).'"'} = $2;
            }
            elsif ($char eq '"' && defined($lstrings{"'".substr($1,1,-1)."'"})) {
                print "+ Found translation for $1: $2\n";
                $lstrings{"'".substr($1,1,-1)."'"} = $2;
            }
            else {
                print "+ Dropping translation $1\n";
            }
        }
    }
    close STRINGS;

    # create the new translation file strings.php.new.

    open(NEWSTRINGS, "> $ss_base/$locale_path/$locale/strings.php.new") or die "Can't create $ss_base/$locale_path/$locale/strings.php.new\n";
    print NEWSTRINGS "<?php\n\t\$phrase = array (\n";
    foreach my $string (keys %lstrings) {
        print NEWSTRINGS "\t\t\t\t$string => ";
        print NEWSTRINGS ($lstrings{$string} ne '') ? $lstrings{$string} : "''";
        print NEWSTRINGS ",\n";
        print "+ New string " . $string . "\n" if ($lstrings{$string} eq '');
    }
    print NEWSTRINGS "\t\t\t);\n?>\n";
    close NEWSTRINGS;
    print "Generated new translation file for $locale as $ss_base/$locale_path/$locale/strings.php.new\n\n";
}


exit 0;

