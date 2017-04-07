SELECT 
    dCat.titre,
    SUBSTRING(dCat.url, -11), dCat.url,
    dDat.titre,
    SUBSTRING(dDat.url, -11)
FROM
    flux_doc dCat
        INNER JOIN
    flux_doc dDat ON SUBSTRING(dCat.url, -11)=SUBSTRING(dDat.url, -11)
WHERE dCat.url LIKE "http://catalogue.bnf.fr/ark:/12148/%" AND dDat.url LIKE "http://data.bnf.fr/ark:/12148/%" ; 