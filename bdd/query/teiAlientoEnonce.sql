SELECT cE.milestone, cE.plaintext, SUBSTRING(cE.milestone,8) idXml
   ,cS.milestone, CS.plaintext, length(CS.plaintext)
FROM tei_xmlchunk cE
 INNER JOIN tei_xmlchunk cS ON cS.xml LIKE CONCAT('%',SUBSTRING(cE.milestone,8),'%') 
WHERE cE.milestone LIKE '%enonce-eno_%' AND length(TRIM(CS.plaintext)) > 1