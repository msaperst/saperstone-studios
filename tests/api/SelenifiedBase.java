import com.coveros.selenified.Browser;
import com.coveros.selenified.Selenified;
import com.coveros.selenified.services.HTTP;
import com.google.gson.JsonArray;
import org.testng.ITestContext;
import org.testng.ITestResult;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;

import java.io.IOException;
import java.lang.reflect.Method;
import java.net.InetAddress;
import java.net.NetworkInterface;
import java.net.UnknownHostException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Enumeration;
import java.util.List;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;

import static com.coveros.selenified.utilities.Constants.*;
import static com.coveros.selenified.utilities.Constants.END_IDIV;
import static org.testng.Assert.assertEquals;
import static org.testng.Assert.assertTrue;

public class SelenifiedBase extends Selenified {

    @BeforeClass(alwaysRun = true)
    public void beforeClass(ITestContext test) {
        setAppURL(this, test, "http://localhost:90/");
        setContentType(this, test, HTTP.ContentType.FORMDATA);
    }

    @BeforeMethod(alwaysRun = true)
    protected void startTest(Object[] dataProvider, Method method, ITestContext test, ITestResult result) throws IOException {
        super.startTest(dataProvider, method, test, result, Browser.BrowserUse.FALSE);
    }

    /**
     * Returns an <code>InetAddress</code> object encapsulating what is most likely the machine's LAN IP address.
     * <p/>
     * This method is intended for use as a replacement of JDK method <code>InetAddress.getLocalHost</code>, because
     * that method is ambiguous on Linux systems. Linux systems enumerate the loopback network interface the same
     * way as regular LAN network interfaces, but the JDK <code>InetAddress.getLocalHost</code> method does not
     * specify the algorithm used to select the address returned under such circumstances, and will often return the
     * loopback address, which is not valid for network communication. Details
     * <a href="http://bugs.sun.com/bugdatabase/view_bug.do?bug_id=4665037">here</a>.
     * <p/>
     * This method will scan all IP addresses on all network interfaces on the host machine to determine the IP address
     * most likely to be the machine's LAN address. If the machine has multiple IP addresses, this method will prefer
     * a site-local IP address (e.g. 192.168.x.x or 10.10.x.x, usually IPv4) if the machine has one (and will return the
     * first site-local address if the machine has more than one), but if the machine does not hold a site-local
     * address, this method will return simply the first non-loopback address found (IPv4 or IPv6).
     * <p/>
     * If this method cannot find a non-loopback address using this selection algorithm, it will fall back to
     * calling and returning the result of JDK method <code>InetAddress.getLocalHost</code>.
     * <p/>
     *
     * @throws UnknownHostException If the LAN address of the machine cannot be found.
     */
    InetAddress getLocalHostLANAddress() throws UnknownHostException {
        try {
            InetAddress candidateAddress = null;
            // Iterate all NICs (network interface cards)...
            for (Enumeration ifaces = NetworkInterface.getNetworkInterfaces(); ifaces.hasMoreElements(); ) {
                NetworkInterface iface = (NetworkInterface) ifaces.nextElement();
                // Iterate all IP addresses assigned to each card...
                for (Enumeration inetAddrs = iface.getInetAddresses(); inetAddrs.hasMoreElements(); ) {
                    InetAddress inetAddr = (InetAddress) inetAddrs.nextElement();
                    if (!inetAddr.isLoopbackAddress()) {

                        if (inetAddr.isSiteLocalAddress()) {
                            // Found non-loopback site-local address. Return it immediately...
                            return inetAddr;
                        } else if (candidateAddress == null) {
                            // Found non-loopback address, but not necessarily site-local.
                            // Store it as a candidate to be returned if site-local address is not subsequently found...
                            candidateAddress = inetAddr;
                            // Note that we don't repeatedly assign non-loopback non-site-local addresses as candidates,
                            // only the first. For subsequent iterations, candidate will be non-null.
                        }
                    }
                }
            }
            if (candidateAddress != null) {
                // We did not find a site-local address, but we found some other non-loopback address.
                // Server might have a non-site-local address assigned to its NIC (or it might be running
                // IPv6 which deprecates the "site-local" concept).
                // Return this non-loopback candidate address...
                return candidateAddress;
            }
            // At this point, we did not find a non-loopback address.
            // Fall back to returning whatever InetAddress.getLocalHost() returns...
            InetAddress jdkSuppliedAddress = InetAddress.getLocalHost();
            if (jdkSuppliedAddress == null) {
                throw new UnknownHostException("The JDK InetAddress.getLocalHost() method unexpectedly returned null.");
            }
            return jdkSuppliedAddress;
        } catch (Exception e) {
            UnknownHostException unknownHostException = new UnknownHostException("Failed to determine LAN address: " + e);
            unknownHostException.initCause(e);
            throw unknownHostException;
        }
    }

    void checkDbEquals(String expected, ResultSet rs, String column) throws SQLException {
        String actual = rs.getString(column);
        if (actual == expected || expected.equals(actual)) {
            this.calls.get().getReporter().pass("", "DB Results contain " + column + " '" + expected + "'", "DB Results contain " + column + " '" + actual + "'");
        } else {
            this.calls.get().getReporter().fail("", "DB Results contain " + column + " '" + expected + "'", "DB Results contain " + column + " '" + actual + "'");
        }
        assertEquals(expected, actual, "DB Results Mismatch");
    }

    void checkDbMatches(String expected, ResultSet rs, String column) throws SQLException {
        String actual = rs.getString(column);
        if (actual.matches(expected)) {
            this.calls.get().getReporter().pass("", "DB Results contain " + column + " '" + expected + "'", "DB Results contain " + column + " '" + actual + "'");
        } else {
            this.calls.get().getReporter().fail("", "DB Results contain " + column + " '" + expected + "'", "DB Results contain " + column + " '" + actual + "'");
        }
        assertTrue(actual.matches(expected), "DB Results Mismatch");
    }

    void assertZipContains(String zipFilePath, List<String> expectedFiles) throws IOException {
        List<String> actualFiles = new ArrayList<>();

        ZipFile zipFile = new ZipFile(zipFilePath);
        Enumeration<? extends ZipEntry> entries = zipFile.entries();

        while (entries.hasMoreElements()) {
            ZipEntry entry = entries.nextElement();
            String name = entry.getName();
            actualFiles.add(name);
        }
        zipFile.close();

        if (expectedFiles.equals(actualFiles)) {
            this.calls.get().getReporter().pass("", "Zip file contains files <b>" + String.join("</b>, <b>", expectedFiles) + "</b>", "Zip file contains files <b>" + String.join("</b>, <b>", actualFiles) + "</b>");
        } else {
            this.calls.get().getReporter().fail("", "Zip file contains files <b>" + String.join("</b>, <b>", expectedFiles) + "</b>", "Zip file contains files <b>" + String.join("</b>, <b>", actualFiles) + "</b>");
        }
        assertEquals(actualFiles, expectedFiles, "ZIP Contents Mismatch");
    }

    void assertArraySize(JsonArray json, int size) {
        if (json.size() == size) {
            this.calls.get().getReporter().pass("", "Expected JsonArray to have size '" + size + "'", "JsonArray has content " + DIV_I + this.calls.get().getReporter().formatHTML(GSON.toJson(json)) + END_IDIV);
        } else {
            this.calls.get().getReporter().fail("", "Expected JsonArray to have size '" + size + "'", "JsonArray has content " + DIV_I + this.calls.get().getReporter().formatHTML(GSON.toJson(json)) + END_IDIV);
        }
        assertEquals(json.size(), size, "Array Size Mismatch");
    }
}