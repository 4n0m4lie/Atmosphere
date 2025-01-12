<?xml version='1.0' encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" indent="yes" />

    <xsl:param name="heureMatin" />
    <xsl:param name="heureMidi" />
    <xsl:param name="heureSoir" />

    <xsl:template match="/">
        <xsl:element name="div">
            <xsl:attribute name="class">meteo</xsl:attribute>
            <xsl:apply-templates />
        </xsl:element>
    </xsl:template>

    <xsl:template match="echeance">
        <xsl:choose>
            <xsl:when test="contains(@timestamp, $heureMatin)">
                <xsl:call-template name="renderEcheance" />
            </xsl:when>
            <xsl:when test="contains(@timestamp, $heureMidi)">
                <xsl:call-template name="renderEcheance" />
            </xsl:when>
            <xsl:when test="contains(@timestamp, $heureSoir)">
                <xsl:call-template name="renderEcheance" />
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="renderEcheance">
        <xsl:element name="div">
            <xsl:element name="p">
                <xsl:if test="contains(@timestamp, $heureMatin)">
                Matin
            </xsl:if>
                <xsl:if test="contains(@timestamp, $heureMidi)">
                    Midi
                </xsl:if>
                <xsl:if test="contains(@timestamp, $heureSoir)">
                    Soir
                </xsl:if>
            </xsl:element>
            <xsl:element name="div">
                <xsl:call-template name="aspectCiel" />
                <xsl:call-template name="vent" />
            </xsl:element>
            <xsl:apply-templates select="temperature" />
        </xsl:element>
    </xsl:template>

    <xsl:template match="echeance/temperature">
        <xsl:apply-templates select="level" />
    </xsl:template>

    <xsl:template match="echeance/temperature/level">
        <xsl:choose>
            <xsl:when test="contains(@val,'2m')">
                <xsl:element name="p">
                    <xsl:value-of select="concat(floor(. - 273.15), ' °C')" />
                </xsl:element>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="aspectCiel">
        <xsl:choose>
            <xsl:when test="pluie > '0' and risque_neige = 'oui'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/freezing-rain.png</xsl:attribute>
                    <xsl:attribute name="alt">Neige et Pluie</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:when test="pluie = '0'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/rainy.png</xsl:attribute>
                    <xsl:attribute name="alt">Pluie</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:when test="risque_neige = 'oui'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/snow.png</xsl:attribute>
                    <xsl:attribute name="alt">Neige</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/sun.png</xsl:attribute>
                    <xsl:attribute name="alt">Soleil</xsl:attribute>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="vent">
        <xsl:choose>
            <xsl:when test="vent_moyen >= '0' and vent_moyen &lt; '20'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/wave.png</xsl:attribute>
                    <xsl:attribute name="alt">vent calme</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:when test="vent_moyen >= '20' and vent_moyen &lt; '50'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/wind.png</xsl:attribute>
                    <xsl:attribute name="alt">vent</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:when test="vent_moyen >= '50' and vent_moyen &lt; '89'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/big_wind.png</xsl:attribute>
                    <xsl:attribute name="alt">vent rafale</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:when test="vent_moyen >= '89' and vent_moyen &lt; '120'">
                <xsl:element name="img">
                    <xsl:attribute name="src">imgMeteo/tornado.png</xsl:attribute>
                    <xsl:attribute name="alt">vent très fort (tempête)</xsl:attribute>
                </xsl:element>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="text()" />
</xsl:stylesheet>
