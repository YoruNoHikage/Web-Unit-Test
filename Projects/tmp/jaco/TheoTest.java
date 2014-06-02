import static org.junit.Assert.*;

import org.junit.After;
import org.junit.Before;
import org.junit.Test;


public class MoneyTest {
	
	protected Money money1, money2;
	
	@Before
	public void setUp() throws Exception {
		money1 = new Money();
		money2 = new Money(2, "KIL");
	}

	@After
	public void tearDown() throws Exception {
	}

	@Test
	public void theo() {		
		assertEquals("1.0 EUR", money1.toString());
	}
	
	@Test
	public void fundone() {		
		assertEquals("2.0 KIL", money2.toString());
	}
	
	@Test
	public void pizza() {	
		money1.add(money1);
		assertEquals("Error potato1 in system", "0.0 EUR", money1.toString());
		assertEquals("Error potato2 in system", "2.0 EUR", money1.toString());
	}
	
	@Test
	public void peperoni() {
		money1.add(money1);
		money1.add(new Money(3, "EUR"));
		assertEquals("3.0 EUR", money1.toString());
	}

}
