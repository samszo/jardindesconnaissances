SELECT s.id, sc.milestone, sc.plaintext
FROM tei_section s
INNER JOIN tei_xmlchunk sc ON sc.section = s.id
WHERE s.element = 'milestone' AND sc.milestone != 'amorce' AND TRIM(sc.plaintext) != ""
ORDER BY sc.milestone
