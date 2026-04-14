CREATE TABLE BONS_VALIDE
(
  DDONS   DATE,
  VALEUR  NUMBER
);


CREATE TABLE MVT_BONS_VALIDE
(
  NUMMVT  NUMBER,
  D_BONS  VARCHAR2(10 BYTE),
  NUMSEM  NUMBER,
  NBRSS   NUMBER,
  NBRSS0  NUMBER,
  NBRSS1  NUMBER,
  NBRSS2  NUMBER,
  MAN     NUMBER,
  MAN1    NUMBER,
  MAN2    NUMBER,
  MSM     NUMBER,
  MSM1    NUMBER,
  MSM2    NUMBER,
  MAD     NUMBER,
  MAD1    NUMBER,
  MAD2    NUMBER,
  TXPMIN  NUMBER,
  TXPMAX  NUMBER,
  TXAMIN  NUMBER,
  TXAMAX  NUMBER,
  TXMP    NUMBER
);

DECLARE
    date_bon DATE;
BEGIN
    SELECT DDONS INTO date_bon
    FROM BONS_VALIDE
    WHERE DDONS = TO_DATE('13/04/2026', 'DD/MM/YYYY');

    DBMS_OUTPUT.PUT_LINE('Date_bon: ' || TO_CHAR(date_bon, 'DD/MM/YYYY'));
END;
/

CREATE OR REPLACE PROCEDURE traiter_bon_simple(p_date DATE) IS
    v_statut VARCHAR2(20);
BEGIN
    -- Logique métier
    IF p_date < SYSDATE - 30 THEN
        v_statut := 'EXPIRE';
    ELSE
        v_statut := 'VALIDE';
    END IF;

    -- Action
    INSERT INTO LOG_BONS(message, date_action)
    VALUES ('Bon ' || v_statut, SYSDATE);

END;
/

-- Exemple simple
CREATE OR REPLACE PROCEDURE traiter_bon(
    p_date DATE,
    p_result OUT VARCHAR2
) IS
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count
    FROM BONS_VALIDE
    WHERE TRUNC(DDONS) = TRUNC(p_date);

    IF v_count > 0 THEN
        p_result := 'OK';
    ELSE
        p_result := 'AUCUNE VALEUR TROUVEE';
    END IF;
END;
/

-- Execution de la procedure
SET SERVEROUTPUT ON;
DECLARE
    v_result VARCHAR2(50);
BEGIN
    traiter_bon(
        TO_DATE('16/04/2026','DD/MM/YYYY'),
        v_result
    );

    DBMS_OUTPUT.PUT_LINE('Résultat : ' || v_result);
END;
/

-- Autre exemple de procedure (retournant plusieurs valeurs)
CREATE OR REPLACE PROCEDURE get_bons(
    p_result OUT SYS_REFCURSOR
) IS
BEGIN
    OPEN p_result FOR
        SELECT NUMMVT, D_BONS, NUMSEM, TXMP
        FROM MVT_BONS_VALIDE;
END;
/

-- Appel de la procedure
SET SERVEROUTPUT ON;
VAR rc REFCURSOR;
BEGIN
    get_bons(:rc);
END;
/
PRINT rc;


-- Exemple de fonction sans parametre
CREATE OR REPLACE FUNCTION get_total RETURN NUMBER IS
    v_total NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_total FROM MVT_BONS_VALIDE;
    RETURN v_total;
END;
/

-- Avec parametre
CREATE OR REPLACE FUNCTION get_bons_by_sem(p_sem NUMBER)
RETURN NUMBER IS
    v_total NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_total
    FROM MVT_BONS_VALIDE
    WHERE NUMSEM = p_sem;

    RETURN v_total;
END;
/

-- Appel de cette fonction
SELECT get_total FROM dual;



-- Fonction qui retourne un tableau de valeur
CREATE OR REPLACE FUNCTION get_bons_function(p_sem NUMBER)
RETURN SYS_REFCURSOR IS
    rc SYS_REFCURSOR;
BEGIN
    OPEN rc FOR
        SELECT NUMMVT, D_BONS, NUMSEM, TXMP
        FROM MVT_BONS_VALIDE where NUMSEM=p_sem;

    RETURN rc;
END;
/

select get_bons_function(4) from dual;