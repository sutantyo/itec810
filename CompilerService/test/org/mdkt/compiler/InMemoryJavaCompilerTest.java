package org.mdkt.compiler;

import java.io.ByteArrayOutputStream;
import java.io.FileDescriptor;
import java.io.FileOutputStream;
import java.io.PrintStream;
import java.io.PrintWriter;
import java.lang.reflect.Method;

import javax.tools.Diagnostic;
import javax.tools.DiagnosticCollector;
import javax.tools.FileObject;
import javax.tools.JavaFileObject;

import org.junit.Assert;
import org.junit.Test;

/**
 * Created by trung on 5/3/15.
 */
public class InMemoryJavaCompilerTest {

    @Test
    public void compile_whenTypical() throws Exception {
        StringBuffer sourceCode = new StringBuffer();

        sourceCode.append("package org.mdkt;\n");
        sourceCode.append("public class HelloClass {\n");
        sourceCode.append("   public String hello() { return \"sup\"; }");
        sourceCode.append("}");

        Class<?> helloClass = InMemoryJavaCompiler.compile("org.mdkt.HelloClass", sourceCode.toString());
        Assert.assertNotNull(helloClass);
        Assert.assertEquals(1, helloClass.getDeclaredMethods().length);
        
        Object instance = helloClass.newInstance();
        Class params[] = {};
        Object paramsObj[] = {};
        Method m = helloClass.getDeclaredMethod("hello", params);
        
        Assert.assertEquals("sup", m.invoke(instance, paramsObj)); 
    }
    
    @Test
    public void capture_output() throws Exception {
        StringBuffer sourceCode = new StringBuffer();

        //sourceCode.append("package org.mdkt;\n");
        sourceCode.append("public class ExampleProgram {\n"); //Had to add public on front????!
        sourceCode.append("   public static void main(String[] args){ System.out.println(\"foo\"); }");
        sourceCode.append("}");

        Class<?> cls = InMemoryJavaCompiler.compile("ExampleProgram", sourceCode.toString());
        Assert.assertNotNull(cls);
        
        Method m = cls.getMethod("main", String[].class);
        String[] params = null;
        
        //Capture start
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        System.setOut(new PrintStream(baos));
        
        m.invoke(null, (Object)params);
        
        //Capture end
        System.out.flush();
        System.setOut(new PrintStream(new FileOutputStream(FileDescriptor.out)));
        String res = baos.toString().trim();
        //System.out.println("res is " + res);
        Assert.assertEquals("foo", res); 
    }
    
    @Test
    public void capture_errors() throws Exception {
        StringBuffer sourceCode = new StringBuffer();
        
        sourceCode.append("public class ExampleProgram {\n"); //Had to add public on front????!
        sourceCode.append("   public static void main(String[] args){ int foo; int foo; System.out.println(\"foo\"); }");
        sourceCode.append("}");
        
        ByteArrayOutputStream berr = new ByteArrayOutputStream();
        System.setErr(new PrintStream(berr));
        
        Class<?> cls = null;
        DiagnosticCollector<JavaFileObject> diagnostics = new DiagnosticCollector<JavaFileObject>();
        //try {
        	 cls = InMemoryJavaCompiler.compile("ExampleProgram", sourceCode.toString() , diagnostics);
        	//cls = InMemoryJavaCompiler.compile("ExampleProgram", sourceCode.toString());
		/*} catch (Exception e) {
			// TODO: handle exception
			System.err.flush();
			System.out.println("err is " + berr.toString());
			Assert.fail("Compilation failed");
			//return;
		}*/ /*finally{
			System.setErr(new PrintStream(new FileOutputStream(FileDescriptor.err)));
		}*/
        Assert.assertNull(cls);
        for (Diagnostic diagnostic : diagnostics.getDiagnostics()){
        	/*System.out.println(diagnostic.getCode());
            System.out.println(diagnostic.getKind());
            System.out.println(diagnostic.getPosition());
            System.out.println(diagnostic.getStartPosition());
            System.out.println(diagnostic.getEndPosition());
            System.out.println(diagnostic.getSource());
            System.out.println(diagnostic.getMessage(null));*/
        	/*System.out.format("Error on line %d in %s%n",
            diagnostic.getLineNumber(),
            ((FileObject) diagnostic.getSource()).toUri());*/
        	System.out.println(diagnostic);
        }
            
        

        //return;
        /*
        Assert.assertNotNull(cls);
        
        Method m = cls.getMethod("main", String[].class);
        String[] params = null;
        
        //Capture start
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        System.setOut(new PrintStream(baos));
        
        
        m.invoke(null, (Object)params);
        
        //Capture end
        System.out.flush();
        
        System.setOut(new PrintStream(new FileOutputStream(FileDescriptor.out)));
        
        String res = baos.toString().trim();
        //System.out.println("res is " + res);
        
        Assert.assertEquals("foo", res); 
        */
    }
    

    
    
}
